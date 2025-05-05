import './bootstrap';
import '../css/app.css';
import $ from 'jquery';
import Alpine from 'alpinejs';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import itLocale from '@fullcalendar/core/locales/it';

window.$ = window.jQuery = $;
window.Alpine = Alpine;

// Definisce una funzione globale "generateGPT" che invia una richiesta AJAX al server
// per generare del testo utilizzando le API di ChatGPT.
// Parametri:
//   pubId: l'ID della pubblicazione per cui generare il testo
//   note: le note inserite dall'utente, che vengono usate per costruire il prompt
window.generateGPT = function(pubId, note) {
    // Esegue una chiamata AJAX con jQuery
    $.ajax({
        // L'URL include l'ID della pubblicazione per indirizzare correttamente la richiesta
        url: `/pubblicazioni/${pubId}/generate-gpt`,
        // Metodo POST per inviare dati al server
        method: "POST",
        // I dati inviati al server includono le note e il token CSRF necessario per la sicurezza
        data: {
            note: note,
            // Legge il token CSRF dal meta tag nella sezione <head> del documento HTML
            // Questo token serve al middleware CSRF di Laravel per verificare la validità della richiesta
            _token: document.querySelector("meta[name='csrf-token']").getAttribute("content")
        },
        // La funzione di callback "success" viene eseguita se la richiesta AJAX ha successo
        success: (response) => {
            // Se la risposta contiene il testo generato (response.generatedText),
            // allora lo inserisce nell'elemento con ID "gpt-output"
            if (response.generatedText) {
                document.getElementById("gpt-output").textContent = response.generatedText;
            } else {
                // Se non viene restituito testo, mostra un messaggio di fallback
                document.getElementById("gpt-output").textContent = "Nessun testo generato.";
            }
        },
        // La funzione di callback "error" viene eseguita se si verifica un errore durante la richiesta AJAX
        error: () => {
            // In caso di errore, imposta il contenuto dell'elemento "gpt-output" a un messaggio di errore
            document.getElementById("gpt-output").textContent = "Errore nella generazione del testo.";
        }
    });
};

// Avvia Alpine.js, che inizializza tutti i componenti dichiarati con x-data, x-ref, ecc.
Alpine.start();

// Array globale per mantenere i file attualmente selezionati dall'utente
let selectedFiles = [];

/**
 * Funzione globale per caricare i file da Nextcloud.
 * Recupera, tramite AJAX, la lista dei file (e cartelle) per un dato percorso.
 *
 * @param {string} path - Il percorso corrente nella struttura dei file (default: '/')
 */
function loadFiles(path = '/') {
    // Ottieni gli elementi del DOM relativi al modale e al suo contenuto
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');

    // Esegui una richiesta AJAX GET verso l'endpoint '/files', passando il percorso desiderato
    $.ajax({
        url: '/files',
        type: 'GET',
        data: { path },
        success: function (response) {
            // Se il contenitore del modale è presente...
            if (modalContent && modal) {
                // Inserisci l'HTML ricevuto (la lista dei file e cartelle) nel contenitore del modale
                modalContent.innerHTML = response;
                // Rendi visibile il modale rimuovendo la classe "hidden"
                modal.classList.remove('hidden');
                // Riapplica la selezione dei file già scelti (se presente)
                reapplySelection();

                // Subito dopo aver iniettato la partial, inizializza i listener per l'upload dei file,
                // passando il percorso corrente (per sapere in quale cartella caricare i file)
                initFileUploadListeners(path);
            } else {
                console.error('Elemento del modale non trovato.');
            }
        },
        error: function () {
            // In caso di errore, mostra un messaggio d'errore nel contenitore del modale
            if (modalContent) {
                modalContent.innerHTML = '<p class="text-red-500">Errore durante il caricamento dei file.</p>';
            }
        },
    });
}

/**
 * Funzione per riapplicare la selezione dei file.
 * Controlla tutti gli elementi con classe "select-file" e, se il loro attributo data-path è presente nell'array selectedFiles,
 * aggiunge la classe "selected-file" al loro elemento contenitore (li).
 */
function reapplySelection() {
    const modal = document.getElementById('modal');
    const modalContent = document.getElementById('modal-content');
    if (!modalContent) return;

    // Ottieni tutti gli elementi con classe "select-file" all'interno del contenitore
    const allFiles = modalContent.querySelectorAll('.select-file');
    allFiles.forEach(el => {
        const filePath = el.getAttribute('data-path');
        const liElement = el.closest('li');
        // Se il file è presente nell'array globale selectedFiles, aggiungi la classe per evidenziare la selezione
        if (selectedFiles.includes(filePath)) {
            liElement.classList.add('selected-file');
        } else {
            liElement.classList.remove('selected-file');
        }
    });
}

/**
 * Funzione per inizializzare il listener sul pulsante "Sfoglia Nextcloud" nella vista dei dettagli.
 * Se il pulsante è presente, al click richiama la funzione loadFiles() per aprire il modale.
 */
function initNextcloudButtonForDetails() {
    const browseNextcloudButton = document.querySelector('#publication-details-container #browse-nextcloud');
    if (browseNextcloudButton) {
        browseNextcloudButton.addEventListener('click', function () {
            loadFiles();
        });
    } else {
        console.log('browse-nextcloud non trovato nella vista dettagli');
    }
}

/**
 * Funzione per inizializzare i listener per il caricamento dei file (upload).
 * Questa funzione cerca gli elementi con ID "uploadFileButton" e "fileToUpload" nel DOM.
 * Viene invocata subito dopo che il modale (file-list) è stato iniettato nel DOM.
 *
 * @param {string} currentPath - Il percorso corrente in cui si sta visualizzando la lista dei file,
 *                               usato per caricare i file nella cartella corretta.
 */
function initFileUploadListeners(currentPath) {
    const uploadButton = document.getElementById('uploadFileButton');
    const fileInput = document.getElementById('fileToUpload');

    if (uploadButton && fileInput) {
        // Aggiungi un listener al click del pulsante di upload
        uploadButton.addEventListener('click', function() {
            const files = fileInput.files;
            // Se non sono stati selezionati file, mostra un messaggio e interrompi
            if (!files || files.length === 0) {
                alert('Seleziona almeno un file da caricare');
                return;
            }

            // Crea un oggetto FormData per inviare i file
            let formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            // Aggiungi al FormData il percorso corrente dove caricare i file
            formData.append('path', currentPath);
            
            // Aggiungi il token CSRF sia come campo nel FormData che nell'header
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            formData.append('_token', csrfToken);

            // Utilizza fetch per inviare una richiesta POST all'endpoint /files/upload
            fetch('/files/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('File caricati con successo!');
                    // Ricarica la lista dei file nella cartella corrente per aggiornare la vista
                    loadFiles(currentPath); 
                } else {
                    alert('Errore durante il caricamento: ' + data.message);
                }
            })
            .catch(err => alert('Errore di rete: ' + err));
        });
    }
}

// FUNZIONI DASHBOARD
// Quando il DOM è completamente caricato, iniziamo a settare gli elementi e la logica
document.addEventListener('DOMContentLoaded', function () {
    // Recupera le variabili globali per gli eventi del calendario e gli elementi del DOM
    const calendarEvents = window.calendarEvents || []; // Array degli eventi del calendario (passato dal backend)
    const clientSelect = document.getElementById(window.clientSelectId); // Selettore per il cliente
    const pubContainer = document.getElementById(window.pubContainerId); // Contenitore del carosello delle pubblicazioni
    const pubDetailsContainer = document.getElementById(window.pubDetailsContainerId); // Contenitore dei dettagli della pubblicazione
    const chatContainer = document.getElementById('pubblicazione-chat'); // Contenitore della chat
    const newPublicationButton = document.getElementById(window.newPublicationButtonId); // Bottone per creare una nuova pubblicazione
    const buttonsContainer = document.getElementById(window.buttonsContainerId); // Container per altri pulsanti (es. Gestisci Asset)
    const manageAssetsButton = document.getElementById(window.manageAssetsButtonId); // Pulsante per gestire gli asset (per amministratori)
    const viewMediaButton = document.getElementById(window.viewMediaButtonId); // Pulsante per la gestione dei media
    const prevSlideButton = document.getElementById(window.prevSlideButtonId); // Bottone per lo slide precedente
    const nextSlideButton = document.getElementById(window.nextSlideButtonId); // Bottone per lo slide successivo
    let currentIndex = 0; // Indice corrente del carosello

    // Variabili per la modalità di modifica
    let isEditMode = false;
    let editButton, saveEditButton, cancelEditButton, editForm, publicationText, publicationDate, publicationDetailsContainer;

    // Configura il calendario: recupera l'elemento e inizializza il calendario con i plugin, locale, vista iniziale e gli eventi passati
    const calendarEl = document.getElementById('calendar');
    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: itLocale,
        initialView: 'dayGridMonth',
        eventSources: [{ events: calendarEvents, id: 'initialEvents' }],
        // La funzione eventContent personalizza il rendering di ciascun evento:
        eventContent: function (arg) {
            // Crea un "dot" che indica lo stato (usando le classNames dell'evento)
            let dotEl = document.createElement('div');
            dotEl.classList.add('status-dot', arg.event.classNames[0]);

            // Crea un elemento che mostra l'orario formattato (se disponibile)
            let timeText = '';
            if (arg.event.start) {
                timeText = new Date(arg.event.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            let titleEl = document.createElement('div');
            titleEl.textContent = `${timeText}`;

            // Restituisce i nodi DOM da inserire per l'evento
            return { domNodes: [dotEl, titleEl] };
        },
    });
    // Renderizza il calendario
    calendar.render();

    /**
     * Aggiorna gli eventi del calendario.
     * Riceve un array di eventi "raw" e li formatta per il calendario.
     *
     * @param {Array} newEvents - Array degli eventi ricevuti (con dati come testo, data_pubblicazione, stato_id, ecc.)
     */
    function updateCalendarEvents(newEvents) {
        const formattedEvents = newEvents.map(event => ({
            title: event.testo || 'Senza titolo',
            start: event.data_pubblicazione,
            classNames: ['stato-' + event.stato_id],
            url: event.url || '#',
        }));

        // Rimuove l'eventSource corrente (con ID "initialEvents") se presente
        const eventSource = calendar.getEventSourceById('initialEvents');
        if (eventSource) eventSource.remove();

        // Aggiunge il nuovo eventSource con gli eventi formattati
        calendar.addEventSource({ events: formattedEvents, id: 'initialEvents' });
    }

    /**
     * Aggiorna il carosello delle pubblicazioni.
     * Imposta il trasform CSS del contenitore in base all'indice corrente.
     */
    function updateCarousel() {
        const totalSlides = pubContainer.children.length;
        prevSlideButton.disabled = currentIndex === 0;
        nextSlideButton.disabled = currentIndex >= totalSlides - 1;
        // Applica una trasformazione per spostare il contenuto in orizzontale
        pubContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    /**
     * Resetta il carosello riportando l'indice corrente a 0 e aggiornando la trasformazione.
     */
    function resetCarousel() {
        currentIndex = 0;
        updateCarousel();
    }

    /**
     * Aggiorna il carosello delle pubblicazioni.
     * Inserisce l'HTML delle pubblicazioni nel contenitore e resetta il carosello.
     *
     * @param {string} publicationsHtml - HTML contenente la lista delle pubblicazioni
     */
    function refreshCarousel(publicationsHtml) {
        $('#pubblicazioni-ul').html(publicationsHtml);
        resetCarousel();
    }

    /**
     * Resetta la sezione dei dettagli della pubblicazione e nasconde la chat.
     */
    function resetPublicationDetails() {
        pubDetailsContainer.innerHTML = '';
        chatContainer.classList.add('hidden'); 
    }

    // Listener sul cambiamento del selettore cliente
    clientSelect.addEventListener('change', function () {
        const clienteId = this.value;
        resetPublicationDetails(); // Resetta i dettagli pubblicazione

        // Se è stato selezionato un cliente, mostra i pulsanti e aggiorna i link relativi
        if (clienteId) {
            buttonsContainer.classList.remove('hidden');
            newPublicationButton.classList.remove('hidden');
            newPublicationButton.href = `/pubblicazioni/clienti/${clienteId}/create`;
            if(manageAssetsButton){
                manageAssetsButton.href = `/clienti/${clienteId}/assets`;
            }
            if(viewMediaButton){
                viewMediaButton.href = `/clienti/${clienteId}/media_pubblicazioni`;
            }
        } else {
            buttonsContainer.classList.add('hidden');
            newPublicationButton.classList.add('hidden');
        }

        // Costruisce l'URL per filtrare le pubblicazioni in base al cliente selezionato (oppure tutte)
        const url = clienteId ? `/filter-publications/${clienteId}` : `/filter-publications/all`;

        // Effettua una richiesta AJAX per ottenere la lista delle pubblicazioni e gli eventi calendario
        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                refreshCarousel(data.pubblicazioniLista);
                updateCalendarEvents(data.pubblicazioniCalendario);
            },
            error: function () {
                console.error('Errore durante il caricamento delle pubblicazioni.');
            },
        });        
    });

    // Listener per il pulsante "slide successivo" del carosello
    nextSlideButton.addEventListener('click', function () {
        const totalSlides = pubContainer.children.length;
        if (currentIndex < totalSlides - 1) {
            currentIndex++;
            updateCarousel();
        }
    });

    // Listener per il pulsante "slide precedente" del carosello
    prevSlideButton.addEventListener('click', function () {
        if (currentIndex > 0) {
            currentIndex--;
            updateCarousel();
        }
    });

    /**
     * Carica i dettagli di una pubblicazione (tramite AJAX) e li inietta nel contenitore.
     * Dopo l'iniezione, esegue varie operazioni di setup (come il carosello, i commenti, ecc.)
     * e dispatcha un evento per aggiornare le note e l'ID della pubblicazione.
     *
     * @param {number|string} id - L'ID della pubblicazione da caricare
     */
    window.loadPublicationDetails = function (id) {
        $.ajax({
            url: `/get-publication-details/${id}`,
            method: 'GET',
            success: function (html) {
                // Inserisce l'HTML dei dettagli della pubblicazione nel contenitore dedicato
                $('#pubblicazione-dettaglio-contenuto').html(html);
    
                // Dopo che Alpine ha aggiornato il DOM (nextTick), esegue ulteriori setup
                Alpine.nextTick(() => {
                    // Se c'è un componente carosello (con x-ref="carouselComponent"), aggiorna selectedFiles
                    const carouselComponent = document.querySelector('[x-ref="carouselComponent"]');
                    if (carouselComponent) {
                        const componentData = Alpine.$data(carouselComponent);
                        selectedFiles = componentData.media.map(m => m.nome);
                    } else {
                        console.log('carouselComponent non trovato dopo il caricamento dei dettagli');
                    }
    
                    // Mostra la chat, carica i commenti, inizializza il pulsante Nextcloud e l'edit mode
                    chatContainer.classList.remove('hidden'); 
                    loadComments(id); 
                    initNextcloudButtonForDetails();
                    initializeEditMode(id);
                    // Ripulisce il contenuto GPT (se presente)
                    $('#gpt-output').html('');
                });
    
                // Recupera i data-attribute dal container appena iniettato per ottenere ID e note della pubblicazione
                const detailEl = document.querySelector('#pubblicazione-dettaglio-contenuto #publication-details-container');
                if (detailEl) {
                    // Prendiamo gli attributi data
                    const pubId = detailEl.getAttribute('data-pub-id');
                    const pubNote = detailEl.getAttribute('data-pub-note') || '';
    
                    // Dispatcha un evento custom "publication-selected" con i dettagli recuperati
                    document.dispatchEvent(
                        new CustomEvent('publication-selected', {
                            detail: {
                                id: pubId,    // l'ID recuperato dai data-attribute
                                note: pubNote // le note recuperate dai data-attribute
                            }
                        })
                    );
                } else {
                    console.warn('publication-details-container non trovato. Non è possibile recuperare ID/note.');
                }
            },
            error: function () {
                console.error('Errore durante il caricamento del dettaglio della pubblicazione.');
            },
        });
    }; 

    /**
     * Carica i commenti per una pubblicazione specifica.
     *
     * @param {number|string} pubblicazioneId - L'ID della pubblicazione
     */
    function loadComments(pubblicazioneId) {
        $.ajax({
            url: `/pubblicazioni/${pubblicazioneId}/commenti`,
            method: 'GET',
            success: function (data) {
                // Imposta l'ID della pubblicazione in un campo nascosto e inietta gli HTML dei commenti
                $('#publication-id').val(pubblicazioneId);
                $('#comment-section').html(data.commentiHtml);
            },
            error: function () {
                console.error('Errore durante il caricamento dei commenti.');
            },
        });
    }

    // Gestione dell'invio dei commenti tramite il form dedicato
    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        const pubblicazioneId = $('#publication-id').val(); 
        const commentText = $('#comment-text').val();

        $.ajax({
            url: `/pubblicazioni/${pubblicazioneId}/commenti`,
            method: 'POST',
            data: {
                commento: commentText,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function () {
                // Dopo l'invio, pulisci il campo commento e ricarica i commenti
                $('#comment-text').val('');
                loadComments(pubblicazioneId);
            },
            error: function () {
                console.error('Errore durante l\'invio del commento.');
            },
        });
    });

    /**
     * Inizializza la modalità di modifica per una pubblicazione.
     * Collega i pulsanti di edit, cancel e save, e prepara il form di modifica.
     *
     * @param {number|string} publicationId - L'ID della pubblicazione da modificare
     */
    function initializeEditMode(publicationId) {
        editButton = document.getElementById('edit-button');
        saveEditButton = document.getElementById('save-edit-button');
        cancelEditButton = document.getElementById('cancel-edit-button');
        editForm = document.getElementById('publication-edit-form');
        publicationText = document.getElementById('publication-text');
        publicationDate = document.getElementById('publication-date');

        if (editButton && saveEditButton && cancelEditButton) {
            // Al click di "Modifica", entra in modalità modifica
            editButton.addEventListener('click', function () {
                toggleEditMode(true);
            });

            // Al click di "Termina Modifica", esce dalla modalità modifica
            cancelEditButton.addEventListener('click', function () {
                toggleEditMode(false);
            });

            // Al click di "Salva Modifiche", invia le modifiche al server
            saveEditButton.addEventListener('click', function () {
                savePublicationChanges(publicationId);
            });
        }
    }

    /**
     * Alterna la modalità di modifica (edit mode).
     * Se enable è true, mostra il form di modifica e nasconde i dettagli; 
     * se false, ripristina la vista originale, inclusa la lista dei media originali.
     *
     * @param {boolean} enable - Indica se attivare o disattivare la modalità modifica.
     */
    function toggleEditMode(enable) {
        isEditMode = enable;
    
        if (isEditMode) {
            // Nascondi i dettagli e mostra il form di modifica
            publicationText.classList.add('hidden');
            publicationDate.classList.add('hidden');
            editForm.classList.remove('hidden');
    
            // Mostra il pulsante "Sfoglia Nextcloud" se presente
            const browseNextcloudButton = editForm.querySelector('#browse-nextcloud');
            if (browseNextcloudButton) {
                browseNextcloudButton.classList.remove('hidden');
            }
    
            // Aggiorna il contenitore dei file selezionati nel form di modifica
            const editContainer = document.getElementById('selected-files-container-edit');
            if (editContainer) {
                editContainer.innerHTML = '';
                selectedFiles.forEach(filePath => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_files[]';
                    input.value = filePath;
                    editContainer.appendChild(input);
                });
            }
    
            // Nascondi il pulsante "Modifica" e mostra "Salva" e "Termina Modifica"
            editButton.classList.add('hidden');
            saveEditButton.classList.remove('hidden');
            cancelEditButton.classList.remove('hidden');
        } else {
            // Uscita dalla modalità modifica:
            // Ripristina la visualizzazione originale del testo e della data
            publicationText.classList.remove('hidden');
            publicationDate.classList.remove('hidden');
            editForm.classList.add('hidden');
    
            // Mostra nuovamente il pulsante "Modifica" e nascondi gli altri
            editButton.classList.remove('hidden');
            saveEditButton.classList.add('hidden');
            cancelEditButton.classList.add('hidden');
    
            // Ripristina il carosello dei media con quelli originali
            const carouselComponent = document.querySelector('[x-ref="carouselComponent"]');
            if (carouselComponent) {
                const componentData = Alpine.$data(carouselComponent);
                // Imposta la lista dei media al valore originale salvato in originalMedia
                componentData.setFiles(componentData.originalMedia.map(item => item.nome));
            }
        }
    }

    /**
     * Salva le modifiche apportate a una pubblicazione.
     * Raccoglie i dati dal form di modifica e li invia via AJAX al server.
     *
     * @param {number|string} publicationId - L'ID della pubblicazione da aggiornare
     */
    function savePublicationChanges(publicationId) {
        // Raccoglie i dati dal form di modifica
        const formData = {
            testo: document.getElementById('publication-edit-text').value,
            data_pubblicazione: document.getElementById('publication-edit-date').value,
            azione: 'invia_al_cliente',
            _method: 'PUT',
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            selected_files: Array.from(
                document.querySelectorAll('#selected-files-container-edit input[name="selected_files[]"]')
            ).map(input => input.value),
        };
    
        const originalDate = document.getElementById('publication-date').textContent.trim();
    
        // Invia una richiesta AJAX POST per aggiornare la pubblicazione
        $.ajax({
            url: `/pubblicazioni/${publicationId}`,
            method: 'POST',
            data: formData,
            success: function (response) {
                alert('Modifiche salvate con successo!');
                toggleEditMode(false);
                const formattedDate = formData.data_pubblicazione.replace('T', ' ');
                document.getElementById('publication-text').textContent = formData.testo;
                document.getElementById('publication-date').textContent = formattedDate;
    
                // Se la data di pubblicazione è cambiata, aggiorna calendario e carosello
                if (originalDate !== formattedDate) {
                    $.ajax({
                        url: '/filter-publications/all',
                        method: 'GET',
                        success: function (data) {
                            updateCalendarEvents(data.pubblicazioniCalendario);
                            refreshCarousel(data.pubblicazioniLista);
                        },
                        error: function () {
                            console.error('Errore durante l\'aggiornamento del calendario e del carosello.');
                        },
                    });
                }
    
                // Se lo stato della pubblicazione è diverso da quelli previsti, nascondi il pulsante di modifica
                if (response.stato_id !== 1 && response.stato_id !== 2) {
                    document.getElementById('edit-button').classList.add('hidden');
                }
            },
            error: function () {
                alert('Errore durante il salvataggio delle modifiche.');
            },
        });
    }

    // Inizializza il carosello e la sezione dettagli al caricamento della dashboard
    resetCarousel();
    resetPublicationDetails(); 
});

// Gestione dei click globali e della selezione dei file all'interno del modale Nextcloud
document.addEventListener('DOMContentLoaded', function () {
    // Recupera gli elementi del DOM relativi al modale e ai suoi pulsanti
    const browseNextcloudButton = document.getElementById('browse-nextcloud'); // Pulsante per aprire la navigazione dei file Nextcloud
    const modal = document.getElementById('modal'); // Contenitore principale del modale
    const modalContent = document.getElementById('modal-content'); // Contenitore dove viene iniettato il contenuto (lista file)
    const closeModalButton = document.getElementById('close-modal'); // Pulsante per chiudere il modale
    const confirmSelectionButton = document.getElementById('confirm-selection'); // Pulsante per confermare la selezione dei file
    
    // Se il pulsante "Sfoglia Nextcloud" esiste, aggiunge un listener per caricare i file
    if (browseNextcloudButton) {
        browseNextcloudButton.addEventListener('click', function () {
            // Chiamata alla funzione loadFiles senza parametro specifico (usa default '/')
            loadFiles(); 
        });
    }

    // Se il pulsante per chiudere il modale esiste, aggiunge un listener per nascondere il modale
    if (closeModalButton) {
        closeModalButton.addEventListener('click', function () {
            if (modal) {
                // Aggiunge la classe 'hidden' per nascondere il modale
                modal.classList.add('hidden');
            }
        });
    }

    // Se il pulsante per confermare la selezione dei file esiste, aggiunge un listener per finalizzare la selezione
    if (confirmSelectionButton) {
        confirmSelectionButton.addEventListener('click', function () {
    
            // Recupera il componente carousel (usato per visualizzare i file)
            const carouselComponent = document.querySelector('[x-ref="carouselComponent"]');
            if (carouselComponent) {
                // Ottiene i dati reattivi di Alpine per il componente
                const componentData = Alpine.$data(carouselComponent);
                // Aggiorna la lista dei file selezionati nel componente utilizzando la funzione setFiles
                componentData.setFiles(selectedFiles);
            } else {
                console.log('carouselComponent non trovato');
            }
    
            // Recupera il contenitore dove inserire gli input hidden per i file selezionati nel form di modifica
            let container = document.getElementById('selected-files-container-edit');
            // Se non esiste il contenitore di modifica, prova a recuperare quello generico
            if (!container) {
                container = document.getElementById('selected-files-container');
            }
            
    
            if (container) {
                // Pulisce il contenitore, eliminando eventuali input hidden precedenti
                container.innerHTML = '';
                // Per ogni file selezionato, crea un input hidden e lo aggiunge al contenitore
                selectedFiles.forEach(filePath => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_files[]';
                    input.value = filePath;
                    container.appendChild(input);
                });
            }
    
            // Nasconde il modale dopo la conferma della selezione
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    }   
    
    // Aggiunge un listener globale per gestire i click su vari elementi all'interno del modale
    document.addEventListener('click', function (event) {
        // Se il click è sul pulsante "go-back", recupera il percorso genitore e carica i file della cartella superiore
        if (event.target && event.target.id === 'go-back') {
            const parentPath = event.target.getAttribute('data-path');
            if (parentPath) {
                loadFiles(parentPath);
            }
            return;  // Interrompe l'esecuzione per evitare ulteriori azioni
        }

        // Gestione della selezione dei file: se il click è su un elemento con classe "select-file", gestisce la selezione
        const selectFileElement = event.target.closest('.select-file');
        if (selectFileElement) {
            // Recupera il percorso del file dal data-path
            const filePath = selectFileElement.getAttribute('data-path');
            // Controlla se l'elemento ha la classe "folder" per distinguere tra file e cartelle
            const isFolder = selectFileElement.classList.contains('folder');
            // Recupera l'elemento list item contenitore dell'elemento selezionato
            const liElement = selectFileElement.closest('li');

            if (isFolder) {
                // Se l'elemento è una cartella, carica i file presenti in quella cartella
                loadFiles(filePath);
            } else {
                // Se è un file, controlla se è già selezionato e aggiorna la classe di evidenziazione
                if (selectedFiles.includes(filePath)) {
                    // Se il file è già selezionato, rimuovilo dall'array e rimuovi la classe evidenziata
                    selectedFiles = selectedFiles.filter(f => f !== filePath);
                    liElement.classList.remove('selected-file');
                } else {
                    // Altrimenti, aggiungi il file all'array e applica la classe di evidenza
                    selectedFiles.push(filePath);
                    liElement.classList.add('selected-file');
                }
            }
        }
    });
});

document.addEventListener('click', function (event) {
    // Controlla se l'elemento cliccato (o uno dei suoi antenati) ha l'ID "pianifica-button"
    const pianificaButton = event.target.closest('#pianifica-button');
    if (pianificaButton) {
        // Recupera l'ID della pubblicazione dal data attribute del container
        const pubDetailsContainer = document.getElementById('publication-details-container');
        const pubId = pubDetailsContainer ? pubDetailsContainer.getAttribute('data-pub-id') : null;
        
        if (!pubId) {
            alert("ID della pubblicazione non trovato.");
            return;
        }
        
        // Invia una richiesta AJAX per aggiornare lo stato a 5 (Pianificata)
        $.ajax({
            url: `/pubblicazioni/${pubId}/pianifica`,
            method: 'POST',
            data: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                if (response.success) {
                    alert('Pubblicazione pianificata con successo!');
                    
                    // Ricarica la pagina per aggiornare automaticamente il calendario e il carosello
                    window.location.reload();
                } else {
                    alert('Errore: ' + response.message);
                }
            },
            error: function() {
                alert('Errore nel passaggio allo stato Pianificata.');
            }
        });
    }
});
