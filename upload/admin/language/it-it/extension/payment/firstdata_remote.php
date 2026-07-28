<?php
// Heading
$_['heading_title']					 = 'First Data EMEA Web Service API';

// Text
$_['text_firstdata_remote']			 = '<img src="view/image/payment/firstdata.png" alt="First Data" title="First Data" style="border: 1px solid #EEEEEE;" />';
$_['text_extension']					 = 'Estensioni';
$_['text_success']					= 'Dettagli Conto First Data modificato correttamente!';
$_['text_edit']                     = 'Modifica Servizio Web API First Data EMEA';
$_['text_card_type']				= 'Tipo Carta';
$_['text_enabled']					= 'Abilitato';
$_['text_merchant_id']				= 'ID Negozio';
$_['text_subaccount']				= 'Subaccount';
$_['text_user_id']					= 'ID Utente';
$_['text_capture_ok']				= 'Acquisizione avvenuta con successo';
$_['text_capture_ok_order']			= 'Acquisizione avvenuta con successo, ordine aggiornato come avvenuto cor successo';
$_['text_refund_ok']				= 'Rimborso effettuato con successo';
$_['text_refund_ok_order']			= 'Ordine Rimborsato con successo ed aggiornato correttamente';
$_['text_void_ok']					= 'Ordine annullato con successo ed aggiornato correttamente';
$_['text_settle_auto']				= 'Vendita';
$_['text_settle_delayed']			= 'Pre Autorizzazione';
$_['text_mastercard']				= 'Mastercard';
$_['text_visa']						= 'Visa';
$_['text_diners']					= 'Diners';
$_['text_amex']						= 'American Express';
$_['text_maestro']					= 'Maestro';
$_['text_payment_info']				= 'Informazioni Pagamento';
$_['text_capture_status']			= 'Pagamento Acquisito';
$_['text_void_status']				= 'Pagamento Annullato';
$_['text_refund_status']			= 'Pagamento Rimborsato';
$_['text_order_ref']				= 'Rif ordine';
$_['text_order_total']				= 'Totale Autorizzato';
$_['text_total_captured']			= 'Totale Acquisito';
$_['text_transactions']				= 'Transazioni';
$_['text_column_amount']			= 'Importo';
$_['text_column_type']				= 'Tipo';
$_['text_column_date_added']		= 'Creato';
$_['text_confirm_void']				= 'Sicuri di voler annullare il pagamento?';
$_['text_confirm_capture']			= 'Sicuri di voler acquisire il pagamento?';
$_['text_confirm_refund']			= 'Sicuri di voler rimborsare il pagamento?';
$_['text_void']                      = 'Nullo';
$_['text_payment']                   = 'Pagamento';
$_['text_refund']                    = "Rimborso";

// Entry
$_['entry_certificate_path']		= 'Percorso Certificato';
$_['entry_certificate_key_path']	= 'Percorso Chiave Privata';
$_['entry_certificate_key_pw']		= 'Password Chaive Privata';
$_['entry_certificate_ca_path']		= 'Percorso CA';
$_['entry_merchant_id']				= 'ID Negozio';
$_['entry_user_id']					= 'ID Utente';
$_['entry_password']				= 'Password';
$_['entry_total']					= 'Totale';
$_['entry_sort_order']				= 'Ordinamento';
$_['entry_geo_zone']				= 'Zona Geografica';
$_['entry_status']					= 'Stato';
$_['entry_debug']					= 'Salvataggio Debug';
$_['entry_auto_settle']				= 'Tipo di liquidazione';
$_['entry_status_success_settled']	= 'Successo - liquidato';
$_['entry_status_success_unsettled'] = 'Successo - non liquidato';
$_['entry_status_decline']			= 'Declinato';
$_['entry_status_void']				= 'Annulla';
$_['entry_status_refund']			= 'Rimborsato';
$_['entry_enable_card_store']		= 'Abilita salvataggio token carta';
$_['entry_cards_accepted']			= 'Tipi di Carte Accettate';

// Help
$_['help_total']					= 'Il totale che deve raggiungere l\'ordine prima che diventi attivo questo metodo di pagamento.';
$_['help_certificate']				= 'Certificate e chiavi private devono essere salvati fuori da cartelle web pubbliche';
$_['help_card_select']				= 'Chiedi all\'utente di scegliere tipi di carta diversi prima di essere reindirizzati';
$_['help_notification']				= 'Inviare questo indirizzo a First Data per avere le notifiche di pagamento';
$_['help_debug']					= 'Abilitando il debug verranno scritte informazioni sensibili in un file di log. Lasciare disabilitato se non richiesto espressamente.';
$_['help_settle']					= 'Se si utilizza la pre-autorizzazione si deve completare un\'azione post-autorizzazione entro 3-5 giorni altrimenti la transazione verr&agrave; annullata';

// Tab
$_['tab_account']					= 'Informazioni API';
$_['tab_order_status']				= 'Stato ordine';
$_['tab_payment']					= 'Informazioni Pagamento';

// Button
$_['button_capture']				= 'Acquisizione';
$_['button_refund']					= 'Rimborso';
$_['button_void']					= 'Annulla';

// Error
$_['error_merchant_id']				= 'ID Negozio obbligatorio';
$_['error_user_id']					= 'ID Utente obbligatorio';
$_['error_password']				= 'Password obbligatoria';
$_['error_certificate']				= 'Percorso Certificato obbligatorio';
$_['error_key']						= 'Chiave Certificato obbligatorio';
$_['error_key_pw']					= 'Password Chiave Certificato obbligatoria';
$_['error_ca']						= 'Autorit&agrave; di Certificazione (CA) obbligatoria';