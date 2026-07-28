<?php
// Heading
$_['heading_title']                 = 'CardConnect';

// Tab
$_['tab_settings']                  = 'Impostazioni';
$_['tab_order_status']              = 'Stato Ordine';

// Text
$_['text_extension']                = 'Estensioni';
$_['text_success']                  = 'Hai modificato con successo CardConnect payment module!';
$_['text_edit']                     = 'Modifica CardConnect';
$_['text_cardconnect']              = '<a href="http://www.cardconnect.com" target="_blank"><img src="view/image/payment/cardconnect.png" alt="CardConnect" title="CardConnect"></a>';
$_['text_payment']                  = 'Pagamento';
$_['text_refund']                   = 'Rimborso';
$_['text_authorize']                = 'Autorizza';
$_['text_live']                     = 'Live';
$_['text_test']                     = 'Test';
$_['text_no_cron_time']             = 'Cron non &egrave; stato ancora eseguito';
$_['text_payment_info']             = 'Informazioni Pagamento';
$_['text_payment_method']           = 'Metodo di Pagamento';
$_['text_card']                     = 'Carta';
$_['text_echeck']                   = 'eCheck';
$_['text_reference']                = 'Riferimento';
$_['text_update']                   = 'Aggiorna';
$_['text_order_total']              = 'Totale Ordine';
$_['text_total_captured']           = 'Totale Acquisito';
$_['text_capture_payment']          = 'Acquisisci pagamento';
$_['text_refund_payment']           = 'Rimborsa Pagamento';
$_['text_void']                     = 'Vuoto';
$_['text_transactions']             = 'Transazioni';
$_['text_column_type']              = 'Tipo';
$_['text_column_reference']         = 'Riferimento';
$_['text_column_amount']            = 'Importo';
$_['text_column_status']            = 'Stato';
$_['text_column_date_modified']     = 'Data Modifica';
$_['text_column_date_added']        = 'Data di Aggiunta';
$_['text_column_update']            = 'Aggiorna';
$_['text_column_void']              = 'Vuoto';
$_['text_confirm_capture']          = 'Sicuro di voler Acquisire il pagamento?';
$_['text_confirm_refund']           = 'Sicuro di voler rimborsare il pagamento?';
$_['text_inquire_success']          = 'Indagine avvenuta con successo';
$_['text_capture_success']          = 'Acquisizione avvenuta con successo';
$_['text_refund_success']           = 'Rimborso avvenuto con successo';
$_['text_void_success']             = 'Richiesta non valida avvenuta con successo';

// Entry
$_['entry_merchant_id']             = 'ID Commerciante';
$_['entry_api_username']            = 'Username API';
$_['entry_api_password']            = 'Password API';
$_['entry_token']                   = 'Token Segreto';
$_['entry_transaction']             = 'Transazione';
$_['entry_environment']             = 'Ambiente';
$_['entry_site']                    = 'Sito';
$_['entry_store_cards']             = 'Salva le Carte';
$_['entry_echeck']                  = 'eCheck';
$_['entry_total']                   = 'Totale';
$_['entry_geo_zone']                = 'Zona Geografica';
$_['entry_status']                  = 'Stato';
$_['entry_logging']                 = 'Debug Logging';
$_['entry_sort_order']              = 'Ordinamento';
$_['entry_cron_url']                = 'URL Cron Job';
$_['entry_cron_time']               = 'Ultima esecuzione Cron Job';
$_['entry_order_status_pending']    = 'In attesa';
$_['entry_order_status_processing'] = 'In elaborazione';

// Help
$_['help_merchant_id']              = 'Il tuo ID commerciante dell&apos;account CardConnect personale.';
$_['help_api_username']             = 'Il tuo nome utente per accedere all\'API CardConnect.';
$_['help_api_password']             = 'La tua password per accedere all\'API CardConnect.';
$_['help_token']                    = 'Inserisci un token casuale per sicurezza o usa quello generato.';
$_['help_transaction']              = 'Scegli \'Pagamento\' per acquisire immediatamente il pagamento o \'Autorizza\' per approvarlo.';
$_['help_site']                     = 'Determina la prima parte dell\'URL dell\'API. Modificare solo se richiesto.';
$_['help_store_cards']              = 'Se desideri archiviare le carte utilizzando la tokenizzazione.';
$_['help_echeck']                   = 'Sia che tu voglia offrire la possibilità di pagare utilizzando un eCheck.';
$_['help_total']                    = 'Il totale del checkout che l\'ordine deve raggiungere prima che questo metodo di pagamento diventi attivo. Deve essere un valore senza segno di valuta.';
$_['help_logging']                  = 'L\'abilitazione del debug scriverà i dati sensibili in un file di registro. Dovresti sempre disabilitare se non diversamente indicato.';
$_['help_cron_url']                 = 'Imposta un cron job per chiamare questo URL in modo che gli ordini vengano aggiornati automaticamente. È progettato per essere eseguito poche ore dopo l\'ultima acquisizione di un giorno lavorativo.';
$_['help_cron_time']                = 'Questa è l\'ultima volta che è stato eseguito l\'URL del cron job.';
$_['help_order_status_pending']     = 'Lo stato dell\'ordine quando l\'ordine deve essere autorizzato dal commerciante.';
$_['help_order_status_processing']  = 'Lo stato dell\'ordine quando l\'ordine viene acquisito automaticamente.';

// Button
$_['button_inquire_all']            = 'Richiedi tutti';
$_['button_capture']                = 'Acquisizione';
$_['button_refund']                 = 'Rimborso';
$_['button_void_all']               = 'Annulla tutto';
$_['button_inquire']                = 'Richiedi';
$_['button_void']                   = 'Nullo';

// Error
$_['error_permission']              = 'Attenzione: non si hanno i permessi per modificare payment CardConnect!';
$_['error_merchant_id']             = 'Merchant ID Obbligatorio!';
$_['error_api_username']            = 'API Username Obbligatorio!';
$_['error_api_password']            = 'API Password Obbligatorio!';
$_['error_token']                   = 'Secret Token Obbligatorio!';
$_['error_site']                    = 'Sito Obbligatorio!';
$_['error_amount_zero']             = 'L\'importo deve essere maggiore di zero!';
$_['error_no_order']                = 'Nessuna informazione sull\'ordine corrispondente!';
$_['error_invalid_response']        = 'Ricevuta risposta non valida!';
$_['error_data_missing']            = 'Dati mancanti!';
$_['error_not_enabled']             = 'Modulo non abilitato!';