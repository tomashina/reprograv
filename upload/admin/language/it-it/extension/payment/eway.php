<?php
// Heading
$_['heading_title']				= 'eWAY Pagamento';

// Text
$_['text_extension']			= 'Estensioni';
$_['text_success']				= 'Hai modificato con successo your eWAY details!';
$_['text_edit']					= 'Modifica eWAY';
$_['text_eway']					= '<a target="_BLANK" href="http://www.eway.com.au/"><img src="view/image/payment/eway.png" alt="eWAY" title="eWAY" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_authorisation']		= 'Autorizzazione';
$_['text_sale']					= 'Vendita';
$_['text_transparent']			= 'Transparent Redirect (payment form on site)';
$_['text_iframe']				= 'IFrame (payment form in window)';
$_['text_connect_eway']	        = 'eWAY aiuta le aziende a elaborare in modo sicuro tutte le principali carte di credito, con prevenzione delle frodi integrata, supporto tecnico 24 ore su 24, 7 giorni su 7 e molto altro ancora. <a target="_blank" href="https://myeway.force.com/success/accelerator-signup?pid=4382&pa=0012000000ivcWf">Clicca qui</a>';
$_['text_eway_image']	        = '<a target="_blank" href="https://myeway.force.com/success/accelerator-signup?pid=4382&pa=0012000000ivcWf"><img src="view/image/payment/eway_connect.png" alt="eWAY" title="eWAY" class="img-fluid" /></a>';

// Entry
$_['entry_paymode']				= 'Metodo di Pagamento';
$_['entry_test']				= 'Mod. Test';
$_['entry_order_status']		= 'Stato Ordine';
$_['entry_order_status_refund'] = 'Stato Ordine Rimborsato';
$_['entry_order_status_auth']	= 'Stato Ordine autorizzati';
$_['entry_order_status_fraud']	= 'Stato Ordine Frode Sospetta';
$_['entry_status']				= 'Stato';
$_['entry_username']			= 'eWAY API Key';
$_['entry_password']			= 'eWAY password';
$_['entry_payment_type']		= 'Tipo di Pagamento';
$_['entry_geo_zone']			= 'Zona Geografica';
$_['entry_sort_order']			= 'Ordinamento';
$_['entry_transaction_method']	= 'Transaction Method';

// Error
$_['error_permission']			= 'Attenzione: non si hanno i permessi per modificare the eWAY payment module';
$_['error_username']			= 'eWAY API Key è obbligatoria!';
$_['error_password']			= 'eWAY password  è obbligatoria!';
$_['error_payment_type']		= 'E\' necessario almeno un tipo di parametro!';

// Help hints
$_['help_testmode']				= 'Imposta su Sì per utilizzare la sandbox di eWAY.';
$_['help_username']				= 'La tua chiave API eWAY dall\'account MYeWAY.';
$_['help_password']				= 'La password API dall\'account eWay.';
$_['help_transaction_method']	= 'Authorizzazione è disponibile solo per le banche Australiane';

// Order page - payment tab
$_['text_payment_info']			= 'Informazioni Pagamento';
$_['text_order_total']			= 'Totale autorizzato';
$_['text_transactions']			= 'Transazioni';
$_['text_column_transactionid'] = 'eWAY ID Transazione';
$_['text_column_amount']		= 'Importo';
$_['text_column_type']			= 'Tipo';
$_['text_column_created']		= 'Creato';
$_['text_total_captured']		= 'Totale Acqusito';
$_['text_capture_status']		= 'Pagamento Acquisito';
$_['text_void_status']			= 'Pagamento annullato';
$_['text_refund_status']		= 'Pagamento rimborsato';
$_['text_total_refunded']		= 'Totale rimborsato';
$_['text_refund_success']		= 'Rimborso avvenuto con successo!';
$_['text_capture_success']		= 'Acquisizione avvenuta con successo!';
$_['text_refund_failed']		= 'Rimborso fallito: ';
$_['text_capture_failed']		= 'Acquisizione fallita: ';
$_['text_unknown_failure']		= 'Ordine o importo non valido';
$_['text_refund']               = 'Rimborso';

$_['text_confirm_capture']		= 'Sicuri di voler acqusire il pagamento?';
$_['text_confirm_release']		= 'Sicuri di voler rilasciare il pagamento?';
$_['text_confirm_refund']		= 'Sicuri di voler rimborsareil pagamento?';

$_['text_empty_refund']			= 'Inserire un importo da rimborsare';
$_['text_empty_capture']		= 'Si prega di inserire un importo da acquisire';

$_['btn_refund']				= 'Rimborso';
$_['btn_capture']				= 'Acquisizione';

// Validation Error codes
$_['text_card_message_V6000']	= 'Error di validazione non definito';
$_['text_card_message_V6001'] 	= 'IP Cliente non valido';
$_['text_card_message_V6002'] 	= 'DeviceID non valido';
$_['text_card_message_V6011'] 	= 'Importo non valido';
$_['text_card_message_V6012'] 	= 'Descrizione fattura non valida';
$_['text_card_message_V6013'] 	= 'Numero fattura non valido';
$_['text_card_message_V6014'] 	= 'Riferimento fattura non valido';
$_['text_card_message_V6015'] 	= 'Codice Valuta non valido';
$_['text_card_message_V6016'] 	= 'Pagamento richiesto';
$_['text_card_message_V6017'] 	= 'Codice valuta pagamento obbligatorio';
$_['text_card_message_V6018'] 	= 'Codice valuta pagamento sconosciuto';
$_['text_card_message_V6021'] 	= 'Nome del titolare della carta obbligatorio';
$_['text_card_message_V6022'] 	= 'Numero di carta richiesto';
$_['text_card_message_V6023'] 	= 'CVN Obbligatorio';
$_['text_card_message_V6031'] 	= 'Numero carta non valido';
$_['text_card_message_V6032'] 	= 'CVN non valido';
$_['text_card_message_V6033'] 	= 'Data scadenza non valido';
$_['text_card_message_V6034'] 	= 'Numero rilascio non valido';
$_['text_card_message_V6035'] 	= 'Data inizio non valida';
$_['text_card_message_V6036'] 	= 'Mese non valido';
$_['text_card_message_V6037'] 	= 'Anno non valido';
$_['text_card_message_V6040'] 	= 'Token Customer Id non valido';
$_['text_card_message_V6041'] 	= 'Cliente Obbligatorio';
$_['text_card_message_V6042'] 	= 'Nome Cliente Obbligatorio';
$_['text_card_message_V6043'] 	= 'Cognome Cliente Obbligatorio';
$_['text_card_message_V6044'] 	= 'Codice Nazione Cliente Obbligatorio';
$_['text_card_message_V6045'] 	= 'Titolo Cliente Obbligatorio';
$_['text_card_message_V6046'] 	= 'Token Customer ID Obbligatorio';
$_['text_card_message_V6047'] 	= 'RedirectURL Obbligatorio';
$_['text_card_message_V6051'] 	= 'Nome non valido';
$_['text_card_message_V6052'] 	= 'Cognome non valido';
$_['text_card_message_V6053'] 	= 'Codice Nazione non valido';
$_['text_card_message_V6054'] 	= 'Email non valida';
$_['text_card_message_V6055'] 	= 'Telefono non valido';
$_['text_card_message_V6056'] 	= 'Cellulare non valido';
$_['text_card_message_V6057'] 	= 'Fax non valido';
$_['text_card_message_V6058'] 	= 'Titolo non valido';
$_['text_card_message_V6059'] 	= 'Redirect URL non valido';
$_['text_card_message_V6060'] 	= 'Redirect URL non valido';
$_['text_card_message_V6061'] 	= 'Riferimento non valido';
$_['text_card_message_V6062'] 	= 'Nome Azienda non valido';
$_['text_card_message_V6063'] 	= 'Descrizione lavoro non valida';
$_['text_card_message_V6064'] 	= 'Strada1 non valida';
$_['text_card_message_V6065'] 	= 'Strada2 non valido';
$_['text_card_message_V6066'] 	= 'Città non valida';
$_['text_card_message_V6067'] 	= 'Stato non valido';
$_['text_card_message_V6068'] 	= 'Codice Postale non valido';
$_['text_card_message_V6069'] 	= 'Email non valida';
$_['text_card_message_V6070'] 	= 'Telefono non valido';
$_['text_card_message_V6071'] 	= 'Cellulare non valido';
$_['text_card_message_V6072'] 	= 'Commenti non validi';
$_['text_card_message_V6073'] 	= 'Fax non valido';
$_['text_card_message_V6074'] 	= 'Url non valida';
$_['text_card_message_V6075'] 	= 'Nome indirizzo spedizione non valido';
$_['text_card_message_V6076'] 	= 'Cognome indirizzo spedizione non valido';
$_['text_card_message_V6077'] 	= 'Strada1 indirizzo spedizione non valida';
$_['text_card_message_V6078'] 	= 'Strada2 indirizzo spedizione non valida';
$_['text_card_message_V6079'] 	= 'Città indirizzo spedizione non valida';
$_['text_card_message_V6080'] 	= 'Stato indirizzo spedizione non valido';
$_['text_card_message_V6081'] 	= 'Codice Postale indirizzo spedizione non valido';
$_['text_card_message_V6082'] 	= 'Email indirizzo spedizione non valido';
$_['text_card_message_V6083'] 	= 'Telefono indirizzo spedizione non valido';
$_['text_card_message_V6084'] 	= 'Nazione indirizzo spedizione non valido';
$_['text_card_message_V6091'] 	= 'Codice paese Sconosciuto';
$_['text_card_message_V6100'] 	= 'Nome Carta non valido';
$_['text_card_message_V6101'] 	= 'Mese scadenza Carta non valido';
$_['text_card_message_V6102'] 	= 'Anno scadenza Carta non valido';
$_['text_card_message_V6103'] 	= 'Mese inizio Carta non valido';
$_['text_card_message_V6104'] 	= 'Anno inizio Carta non valido';
$_['text_card_message_V6105'] 	= 'Banca di emissione Carta non valido';
$_['text_card_message_V6106'] 	= 'CVN Carta non valido';
$_['text_card_message_V6107'] 	= 'Codice di accesso non valido';
$_['text_card_message_V6108'] 	= 'CustomerHostAddress non valido';
$_['text_card_message_V6109'] 	= 'UserAgent non valido';
$_['text_card_message_V6110'] 	= 'Numero Carta non valido';
$_['text_card_message_V6111'] 	= 'Accesso API non autorizzato - Account non certificato PCI';
$_['text_card_message_V6112'] 	= 'Dettagli della carta ridondanti diversi da anno e mese di scadenza';
$_['text_card_message_V6113'] 	= 'Transazione non valida per il rimborso';
$_['text_card_message_V6114'] 	= 'Errore di convalida del gateway';
$_['text_card_message_V6115'] 	= 'DirectRefundRequest, ID transazione non valido';
$_['text_card_message_V6116'] 	= 'Dati della carta non validi su TransactionID originale';
$_['text_card_message_V6124'] 	= 'Elementi pubblicitari non validi. Gli elementi pubblicitari sono stati forniti, tuttavia i totali non corrispondono al campo Importo totale';
$_['text_card_message_V6125'] 	= 'Tipo di pagamento selezionato non abilitato';
$_['text_card_message_V6126'] 	= 'Numero di carta crittografato non valido, decrittografia non riuscita';
$_['text_card_message_V6127'] 	= 'CVN crittografato non valido, decrittografia non riuscita';
$_['text_card_message_V6128'] 	= 'Metodo non valido per il tipo di pagamento';
$_['text_card_message_V6129'] 	= 'La transazione non &egrave; stata autorizzata per Acquisizione/Annullamento';
$_['text_card_message_V6130'] 	= 'Errore generico nelle informazioni del cliente';
$_['text_card_message_V6131'] 	= 'Errore di informazioni di spedizione generico';
$_['text_card_message_V6132'] 	= 'La transazione è già stata completata o annullata, operazione non consentita';
$_['text_card_message_V6133'] 	= 'Checkout non disponibile per il tipo di pagamento';
$_['text_card_message_V6134'] 	= 'ID transazione di autenticazione non valido per acquisizione / annullamento';
$_['text_card_message_V6135'] 	= 'Errore durante l\'elaborazione del rimborso di PayPal';
$_['text_card_message_V6140'] 	= 'L\'account commerciante è sospeso';
$_['text_card_message_V6141'] 	= 'Dettagli dell\'account PayPal o firma API non validi';
$_['text_card_message_V6142'] 	= 'Autorizzazione non disponibile per Banca / Filiale';
$_['text_card_message_V6150'] 	= 'Importo del rimborso non valido';
$_['text_card_message_V6151'] 	= 'Importo del rimborso superiore alla transazione originale';
$_['text_card_message_D4406'] 	= 'Errore sconosciuto';
$_['text_card_message_S5010'] 	= 'Errore sconosciuto';