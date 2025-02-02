<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    private $baseUri = 'URL-nextcould-DAV';
    private $username = 'username-nextcloud';
    private $password = 'password.nextcloud'; // Da sostituire dopo i test

    /**
     * Mostra l'elenco dei file in una directory.
     * Gestisce sia richieste normali che AJAX. In caso di AJAX, ritorna la vista parziale.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Recupera il percorso richiesto o radice '/'
        $path = $request->query('path', '/');

        try {

            // Recupera i file
            $files = $this->listFilesWithCurl($path);

            // Calcola il parentPath: se non siamo nella root, estraiamo la directory superiore
            $parentPath = $this->getParentPath($path);

            // Se la richiesta è AJAX (utilizzata dal modale)
            if ($request->ajax()) {
                return view('files.file-list', [
                    'files' => $files,
                    'parentPath' => $parentPath, // Passiamo il parentPath alla vista
                ])->render();
            }

            // Altrimenti restituisci la vista intera (non usata in questo caso)
            return view('files.index', [
                'files' => $files,
                'currentPath' => $path,
                'parentPath' => $parentPath,
            ]);
        } catch (\Exception $e) {
            Log::error('Errore durante il recupero dei file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->withErrors("Errore: " . $e->getMessage());
        }
    }

    /**
     * Restituisce il percorso genitore dato un percorso.
     * Se siamo nella root ('/'), non c'è parent.
     *
     * @param string $path
     * @return string|null
     */
    private function getParentPath($path)
    {
        if ($path === '/' || $path === '') {
            // Siamo nella root, nessun parent
            return null;
        }

        $parent = dirname($path);

        // Se dirname restituisce '.', significa che non c'è un parent valido, imposta come root
        if ($parent === '.' || $parent === '') {
            $parent = '/';
        }

        return $parent;
    }

    /**
     * Normalizza il percorso codificando i segmenti per evitare errori.
     *
     * @param string $path
     * @return string
     */
    private function normalizePath($path)
    {
        // Dividi il percorso in segmenti
        $segments = explode('/', trim($path, '/'));

        // Codifica i segmenti
        $encodedSegments = array_map(function ($segment) {
            return rawurlencode(rawurldecode($segment));
        }, $segments);

        // Ricostruisci il percorso
        $encodedPath = implode('/', $encodedSegments);

        return $encodedPath;
    }

    /**
     * Recupera i file da Nextcloud usando cURL.
     * Se il path è '/', mostra la root. Altrimenti naviga nella sottocartella.
     *
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function listFilesWithCurl($path = '/')
    {
        // Rimuovi slash iniziale per evitare doppie barre
        $cleanPath = ltrim($path, '/');

        // Rimuovi eventuali prefissi già presenti
        if (strpos($cleanPath, 'prefisso-da-eliminare') === 0) {
            $cleanPath = substr($cleanPath, strlen('prefisso-da-eliminare'));
        }

        // Normalizza il percorso
        $cleanPath = $this->normalizePath($cleanPath);

        // Costruisci l'URL finale
        $url = rtrim($this->baseUri, '/') . '/' . $cleanPath;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Depth: 1',
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('Errore cURL: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 207) {
            throw new \Exception("Errore HTTP: $httpCode, Risposta: $response");
        }

        curl_close($ch);

        return $this->parseXmlResponse($response);
    }

    /**
     * Analizza la risposta XML di Nextcloud e restituisce un array con i file e le cartelle.
     *
     * @param string $response
     * @return array
     */
    private function parseXmlResponse($response)
    {
        $xml = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml->registerXPathNamespace('d', 'DAV:');

        $files = [];
        foreach ($xml->xpath('//d:response') as $resp) {
            $href = (string)$resp->xpath('d:href')[0];
            $name = urldecode(basename($href));
            $type = isset($resp->xpath('d:propstat/d:prop/d:resourcetype/d:collection')[0]) ? 'folder' : 'file';

            // Aggiunge l'elemento all'array
            $files[] = [
                'name' => $name,
                'path' => $this->extractRelativePath($href),
                'type' => $type,
            ];
        }

        // Rimuove il primo elemento se è la directory corrente (spesso Nextcloud la lista)
        $files = array_filter($files, function($f) {
            // Esclude la cartella corrente se il nome è vuoto
            return $f['name'] !== '';
        });

        return array_values($files);
    }

    /**
     * Estrae il percorso relativo dall'href completo.
     * L'href è in forma assoluta, 
     * da cui recuperiamo solo la parte relativa.
     *
     * @param string $href
     * @return string
     */
    private function extractRelativePath($href)
    {
        // Rimuoviamo la parte iniziale per ottenere percorso relativo
        $prefix = 'prefisso-url';
        if (strpos($href, $prefix) === 0) {
            $rel = substr($href, strlen($prefix));
            // Se finisce con '/', toglilo
            $rel = rtrim($rel, '/');
            return $rel === '' ? '/' : '/' . $rel;
        }

        return '/'; // Se non trova nulla, è la root
    }

    public function showFile($path)
    {
        // Normalizza il percorso
        $cleanPath = ltrim($path, '/');
        $cleanPath = $this->normalizePath($cleanPath);

        // Costruisci l'URL finale per recuperare il file
        $url = rtrim($this->baseUri, '/') . '/' . $cleanPath;

        // Effettua la richiesta cURL per il file binario
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // per seguire eventuali redirect

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (curl_errno($ch)) {
            return response('Errore nel recupero del file', 500);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            return response("File non trovato o errore HTTP: $httpCode", 404);
        }

        // Ritorna il file binario come risposta con il content type rilevato
        return response($response, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'inline; filename="'.basename($path).'"');
    }
    
    public function upload(Request $request)
    {
        // Valida i dati
        $request->validate([
            'files.*' => 'required|file|max:20480',  // 20MB per singolo file
            'path' => 'required|string',
        ]);

        // File caricati dal form data
        $uploadedFiles = $request->file('files'); // array di oggetti UploadedFile
        $path = $request->input('path');          // cartella dove caricare su Nextcloud

        // Carichiamo ciascun file su Nextcloud via cURL (metodo PUT su WebDAV)
        foreach ($uploadedFiles as $file) {
            $fileName = $file->getClientOriginalName();

            // Normalizza e codifica path e nome
            $normalizedPath = $this->normalizePath(rtrim($path, '/') . '/' . $fileName);
            // Costruisci l'URL Nextcloud
            $url = rtrim($this->baseUri, '/') . '/' . $normalizedPath;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
            curl_setopt($ch, CURLOPT_PUT, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Leggi il contenuto del file dal server
            $fileStream = fopen($file->getRealPath(), 'r');
            curl_setopt($ch, CURLOPT_INFILE, $fileStream);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file->getRealPath()));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            fclose($fileStream);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode > 299) {
                // Se c'è errore
                return response()->json([
                    'success' => false,
                    'message' => "Errore HTTP: $httpCode"
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
        ], 200);
    }
}
