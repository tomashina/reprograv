<?php
class ModelExtensionModuleItitLanguage extends Model {
	
	public function islanguageinstalledbycode($lang) {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($lang) . "';");
		return $query->row['total'];
	}
	
	public function installationtype($lang) {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "language` WHERE `code` = '" . $this->db->escape($lang) . "';");
		return $query->row['total'];
	}
	
	
	public function detectlanguageinstallation() {
		$detection = 0;
		/*	roughly detecting the base installation */
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "tax_rate` WHERE `name` = 'IVA (22.0%)';");
		if($query->row['total'] >0) { $detection = 1; }

		/*	roughly detecting the extra translations */
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "layout` WHERE `name` = 'Pagina Iniziale';");
		if($query->row['total'] >0) { $detection = 2; }
		
		return $detection;
	}
	//end of function detectlanguageinstallation

	
	public function sqlinstall($selection = 0) {
		$sql = '';
			
		//the selection can be only 0/empty or 1/anyvalidvalue
		$selection = empty($selection) ? 0 : intval($selection);
		
		
		if($selection > 2) { return false;}
		

		// main sql queries
$sqlsel[0] = <<<'NOWDOCEND'

-- Main Language
-- getting the language_id of the english language or the first language available. 0 as fallback if there's no language available
SET @englishlanguageid = ( COALESCE( (SELECT `language_id` FROM `oc_language` WHERE `code` = 'en-gb' LIMIT 1), (SELECT `language_id` FROM `oc_language` WHERE `code` <> 'it-it' LIMIT 1), 0 ) );

INSERT INTO `oc_language` (`name`, `code`, `locale`, `image`, `directory`, `sort_order`, `status`) VALUES ('Italiano', 'it-it', 'it_IT.UTF-8,it_IT,italian', 'it.png', 'it-it', 1, 1);

SET @languageid = (SELECT `language_id` FROM `oc_language` WHERE `code` = 'it-it');


-- duplicating any existent oc_customer_group_description from english to our language
INSERT IGNORE INTO `oc_customer_group_description` (`customer_group_id`, `language_id`, `name`, `description`) SELECT `customer_group_id`, @languageid, `name`, `description` FROM `oc_customer_group_description` WHERE language_id = @englishlanguageid;
-- translating the copied default customers group if available
UPDATE IGNORE `oc_customer_group_description` SET `name` = 'Predefinito', `description` =  'Clienti'  WHERE `language_id` = @languageid AND `name` = 'Default' AND `description` = 'test';


INSERT INTO `oc_geo_zone` (`name`, `description`, `date_modified`, `date_added`) VALUES ('IVA - Italia', 'Zona - IVA Territorio Italiano', NOW(), NOW() );

SET @vatgeozoneita = (SELECT `geo_zone_id` FROM `oc_geo_zone` WHERE `name` = 'IVA - Italia');

INSERT INTO `oc_zone_to_geo_zone` (`country_id`, `zone_id`, `geo_zone_id`, `date_added`, `date_modified`) VALUES (105,	0, @vatgeozoneita,	NOW(), NOW() );

INSERT INTO `oc_tax_rate` ( `geo_zone_id`, `name`, `rate`, `type`, `date_added`, `date_modified`) VALUES (@vatgeozoneita, 'IVA (22.0%)', '22.0000', 'P', NOW(), NOW() );

SET @taxrateid = (SELECT tax_rate_id FROM `oc_tax_rate` WHERE `name` = 'IVA (22.0%)');

-- adding the tax_rule with tax_class and tax rate for the default opencart - Taxable Goods
INSERT INTO `oc_tax_rule` (`tax_class_id`, `tax_rate_id`, `based`, `priority` ) SELECT `tc`.`tax_class_id`, @taxrateid, 'shipping', 1 FROM `oc_tax_class` tc WHERE `tc`.`tax_class_id` = 9 AND NOT EXISTS (SELECT * FROM `oc_tax_rule` tr WHERE `tr`.`tax_class_id` = 9 AND `tr`.`tax_rate_id` = @taxrateid);

-- adding the tax_rule with tax_class and tax rate for the default opencart - Downloadable products
INSERT INTO `oc_tax_rule` (`tax_class_id`, `tax_rate_id`, `based`, `priority` ) SELECT `tc`.`tax_class_id`, @taxrateid, 'shipping', 1 FROM `oc_tax_class` tc WHERE `tc`.`tax_class_id` = 10 AND NOT EXISTS (SELECT * FROM `oc_tax_rule` tr WHERE `tr`.`tax_class_id` = 10 AND `tr`.`tax_rate_id` = @taxrateid);

-- assigning the IVA 22% tax_rate_id to ALL the customer groups - IGNORE if already exists
INSERT IGNORE INTO `oc_tax_rate_to_customer_group` SELECT @taxrateid, `customer_group_id` FROM `oc_customer_group`;


-- translations of the default information - Ignore if they already exist with the same ID and Language
INSERT IGNORE INTO `oc_information_description` (`information_id`,`title`,  `description`, `language_id`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (3, 'Privacy e Cookie',  '<div><h2>Nota Informativa ex art. 13 del D. Lgs. 196/2003</h2><p>I dati personali degli utenti forniti con la procedura di registrazione sono trattati in forma autorizzata.</p><p>Il responsabile del sito considera e tratta le informazioni sui dati personali che l&apos;Utente inserisce e diffonde tramite il Servizio, alla stregua della corrispondenza privata.</p><p>Il responsabile del sito pertanto, non controllerà, diffonderà autonomamente, né comunicherà tali informazioni, così come le informazioni relative all&apos;Utente, salvo quanto previsto nelle presenti note.</p><p>I dati personali dell&apos;Utente verranno trattati:</p><p>per la gestione, erogazione dei diversi servizi e per la relativa assistenza</p><p>per l&apos;elaborazione di analisi statistiche e di mercato</p><p>per l&apos;invio di comunicazioni relative a servizi e iniziative il responsabile del sito</p><p>per l&apos;invio di informazioni relative a servizi business-to-business e business-to consumer e sulle eventuali promozioni e/o servizi presentati sul sito. L&apos;Utente riconosce che il responsabile del sito potrà intervenire sulle predette informazioni, ove ritenga in buona fede che tale attività sia necessaria per:</p><p>a. conformarsi a prescrizioni di legge;</p><p>b. adeguarsi a un provvedimento legale, inclusa una disposizione dell&apos;Autorità Giudiziaria o di altra autorità competente;</p><p>c. far valere i propri diritti ai sensi delle presenti note;</p><p>d. difendersi da contestazioni di terzi che asseriscano che tali contenuti violano i loro diritti;</p><p>e. tutelare gli interessi del responsabile del sito o di terzi.</p><p>L&apos;Utente riconosce e concorda che il responsabile del sito potrà avere accesso alle informazioni o ai contenuti diffusi tramite il Servizio, in tutti i casi in cui tale accesso si renda necessario al fine di individuare o risolvere un problema tecnico, ovvero per rispondere a reclami relativi al Servizio.</p><p>L&apos;Utente riconosce e concorda che l&apos;elaborazione e il trattamento tecnico delle informazioni è o può rendersi necessario per:</p><p>f. inviare o ricevere tali dati;</p><p>g. eseguire le funzioni di pianificazione o programmazione;</p><p>h. conformarsi alle specifiche tecniche richieste dai network di connessione in rete;</p><p>i. conformarsi alle specifiche tecniche del Servizio.</p><p>Il responsabile del sito è autorizzato a trattare ed archiviare nei propri database il nominativo, il tipo di servizio, i resoconti dell&apos;attività di servizio ed altre informazioni dell&apos;Utente, che riguardino il presente Contratto o qualsiasi Servizio fornito in virtù di esso.</p><p>L&apos;Utente autorizza il trattamento da parte del responsabile del sito di tali dati per le finalità inerenti l&apos;esecuzione del presente Contratto e la prestazione dei servizi in esso contemplati.</p><p>Inoltre, ai sensi dell&apos;art. 7 del D. Lgs. 196/2003, l&apos;Utente autorizza il responsabile del sito all&apos;archiviazione, all&apos;elaborazione e alla comunicazione di dati relativi all&apos;Utente, con le seguenti finalità: servizio clienti (amministrazione, contabilità, gestione dei contratti, ordini, fatturazione, recupero crediti), marketing, promozione, analisi statistiche, indagini sulla soddisfazione della clientela, revisioni, archiviazione di dati storici, informazioni pre-contrattuali.</p></div><div><h2>I cookie</h2><h3>Cosa sono i Cookie</h3><p>I cookie sono piccoli file di testo memorizzati nel computer o nel dispositivo mobile dell´utente quando visita il nostro sito web.</p></div><div><h3>A cosa servono i Cookie</h3><p>I cookie sono da noi utilizzati per assicurare all´utente la migliore esperienza nel nostro sito.Questo sito utilizza i cookie, anche di terze parti, per inviare messaggi promozionali personalizzati.</p></div><div><h3>Tipologie di cookie</h3><p>I cookie sono categorizzati come segue:<ol><li>Cookie di sessione. Cookie automaticamente cancellati quando l&apos;utente chiude il browser.</li><li>Cookie persistenti. Cookie che restano memorizzati nel dispositivo dell´utente sino al raggiungimento di una determinata data di terminazione (in termini di minuti, giorni o anni dalla creazione/aggiornamento del cookie).</li><li>Cookie di terze parti. Cookie memorizzati per conto di soggetti terzi.</li></ol>E possibile controllare ed eliminare i singoli cookie utilizzando le impostazioni della maggior parte dei browser. Ciò, tuttavia, potrebbe impedire di utilizzare correttamente alcune funzioni del nostro sito web.<p>Per saperne di più è possibile fare riferimento a <a href=&quot;http://www.aboutcookies.org&quot; rel=&quot;nofollow&quot;>www.aboutcookies.org</a> o <a href=&quot;http://www.allaboutcookies.org.&quot; rel=&quot;nofollow&quot;>www.allaboutcookies.org.</a></p></div>', @languageid, 'Privacy e Cookie', 'Privacy e Cookie', 'Privacy, Cookie');
INSERT IGNORE INTO `oc_information_description` (`information_id`,`title`,  `description`, `language_id`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (4, 'Chi siamo',  '&lt;p&gt;Chi siamo&lt;/p&gt;', @languageid, 'Chi siamo', 'Chi siamo', 'Chi siamo');
INSERT IGNORE INTO `oc_information_description` (`information_id`,`title`,  `description`, `language_id`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (5, 'Termini e condizioni',  '&lt;p&gt;Termini e condizioni&lt;/p&gt;', @languageid, 'Termini e Condizioni E-Commerce', 'Tutti i termini e condizioni della nostra attivit&agrave; di vendita.', 'Termini, condizioni');
INSERT IGNORE INTO `oc_information_description` (`information_id`,`title`,  `description`, `language_id`, `meta_title`, `meta_description`, `meta_keyword`) VALUES (6, 'Info Spedizioni',  '<h3>Costi Spedizione & Tempi</h3><span>Alcuni metodi di spedizione offerti sono &#34;tracciabili&#34; e, quindi, possono essere costantemente seguiti durante le varie tappe della spedizione. Questo servizio, però, non è disponibile per tutti gli ordini. Avrete la possibilità di scegliere il metodo di spedizione del vostro pacco direttamente sulla pagina degli acquisti.</span></p><p>Gli ordini effettuati vengono normalmente consegnati il giorno lavorativo successivo a quello dell&apos;ordine.Le spedizioni all&apos;interno dell&apos;UE possono richiedere oltre 7 giorni lavorativi. Le spedizione verso i paesi extra-UE possono richiedere oltre 2 settimane.</p><p><span>Gli ordini che vengono spediti con nr di Tracking tendono ad arrivare con maggiore velocit&agrave;.Appena il collo viene consegnato al corriere riceverete un numero di Tracking che vi fornirà dettagli sulla situazione della consegna.</span></p><h3>Esclusione di responsabilità:</span></h3><p><span>1. L\&apos;obiettivo principale &egrave; di garantire che l&apos;ordine arrivi a destinazione il prima possibile. Sono però i clienti e/o destinatari ad assumersi la responsabilità, al momento dell&apos;acquisto, dei prodotti ricevuti e dei dettagli di consegna forniti (controllare sempre che siano corretti).</span></p><p><span>2. Non &egrave; possibile assumerci nessuna responsabilità per eventuali perdite sostenute dal cliente o dal destinatario, in termini di spese di trasporto non rimborsabili e/o costi aggiuntivi per la consegna della merce o per la sostituzione di questa.</span></p><p><span>3. Nel caso in cui riceviate un articolo errato o danneggiato, vi invitiamo a contattarci telefonicamente o via email entro tre giorni dalla data di ricezione dell&apos;ordine, preferibilmente con una fotografia del pacco e del suo contenuto.</span></span></p><p><span> Si prega di non rimandare prodotti senza prima averci contattato ed aver ricevuto un numero RMA.</span></span></p><p><span> I rimborsi sono possibili, da parte dello spedizioniere, se con spedizione assicurata. I costi per la spedizione di partenza e il dazio per l&apos;importazione non sono rimborsabili. Stesso discorso vale per i costi sostenuti per il reso e per l&apos;assicurazione dell&apos;articolo, salvo accordi diversi.</span></p><p><span> Come già riportato in &#34;Termini &amp; Condizioni&#34; la responsabilità è a carico del cliente/destinatario una volta che l&apos;ordine viene spedito.</span></p>', @languageid, 'Info Spedizioni', 'Info Spedizioni', 'Spedizioni');


-- translations of voucher, stock status, return status, return action, order status, oc_subscription_status - Ignore if they already exist with the same ID and Language
INSERT IGNORE INTO `oc_voucher_theme_description` (`voucher_theme_id`, `language_id`, `name`) VALUES (6, @languageid, 'Natale'),(7, @languageid, 'Compleanno'),(8, @languageid, 'Generale');
INSERT IGNORE INTO `oc_stock_status` (`stock_status_id`, `language_id`, `name`) VALUES (7, @languageid, 'Disponibile'),(8, @languageid, 'Su Ordinazione'),(5, @languageid, 'Non disponibile'),(6, @languageid, 'In 2-3 Giorni');
INSERT IGNORE INTO `oc_return_status` (`return_status_id`, `language_id`, `name`) VALUES (1, @languageid, 'In lavorazione'),(3, @languageid, 'Completato'),(2, @languageid, 'In attesa del prodotto');
INSERT IGNORE INTO `oc_return_reason` (`return_reason_id`, `language_id`, `name`) VALUES (1, @languageid, 'Arrivato Danneggiato (DOA)'),(2, @languageid, 'Ricevuto prodotto Errato'),(3, @languageid, 'Errore Ordine'),(4, @languageid, 'Difettoso. Si prega di fornire dettagli'),(5, @languageid, 'Altro. Si prega di fornire dettagli');
INSERT IGNORE INTO `oc_return_action` (`return_action_id`, `language_id`, `name`) VALUES (1, @languageid, 'Rimborsato'),(2, @languageid, 'Credito Inviato'),(3, @languageid, 'Sostituzione Inviata');
INSERT IGNORE INTO `oc_order_status` (`order_status_id`, `language_id`, `name`) VALUES (1, @languageid, 'In corso'),(2, @languageid, 'In Lavorazione'),(3, @languageid, 'Spedito'),(5, @languageid, 'Completato'),(7, @languageid, 'Cancellato'),(8, @languageid, 'Rifiutato'),(9, @languageid, 'Restituzione Annullata'),(10, @languageid, 'Fallito'),(11, @languageid, 'Rimborsato'),(12, @languageid, 'Restituito'),(13, @languageid, 'Stornato'),(14, @languageid, 'Scaduto'),(15, @languageid, 'Processato'),(16, @languageid, 'Annullato');


-- translations of length and weight classes - Ignore if they already exist with the same ID and Language
INSERT IGNORE INTO `oc_length_class_description` (`length_class_id`, `language_id`, `title`, `unit`) VALUES (1, @languageid, 'Centimetri', 'cm'), (2, @languageid, 'Millimetri', 'mm'), (3, @languageid, 'Pollici', 'in');
INSERT IGNORE INTO `oc_weight_class_description` (`weight_class_id`, `language_id`, `title`, `unit`) VALUES (1, @languageid, 'Chilogrammi', 'kg'), (2, @languageid, 'Grammi', 'g'), (5, @languageid, 'Libbre ', 'lb'), (6, @languageid, 'Once', 'oz');

-- translations of the default option descriptions - Ignore if they already exist with the same ID and Language
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('1', @languageid, 'Bottoni Circolari');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('2', @languageid, 'Casella di Controllo');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('4', @languageid, 'Testo');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('6', @languageid, 'Area di Testo');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('8', @languageid, 'Data');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('7', @languageid, 'File');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('5', @languageid, 'Selezioni');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('9', @languageid, 'Ora');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('10', @languageid, 'Data e Ora');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('12', @languageid, 'Data di Consegna');
INSERT IGNORE INTO `oc_option_description`(`option_id`, `language_id`, `name`) VALUES ('11', @languageid, 'Dimensioni');

-- translations of the default option values descriptions - Ignore if they already exist with the same ID and Language
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('43', @languageid, '1', 'Largo');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('32', @languageid, '2', 'Piccolo');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('45', @languageid, '2', 'Checkbox 4');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('44', @languageid, '1', 'Checkbox 3');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('31', @languageid, '5', 'Medio');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('42', @languageid, '5', 'Giallo');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('41', @languageid, '5', 'Verde');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('39', @languageid, '5', 'Rosso');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('40', @languageid, '5', 'Blu');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('23', @languageid, '2', 'Checkbox 1');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('24', @languageid, '2', 'Checkbox 2');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('48', @languageid, '11', 'Largo');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('47', @languageid, '11', 'Medio');
INSERT IGNORE INTO `oc_option_value_description`(`option_value_id`, `language_id`, `option_id`, `name`) VALUES ('46', @languageid, '11', 'Piccolo');



-- duplicating descriptions from the languange id 1. In most cases it's the default english language
INSERT IGNORE INTO `oc_category_description`(`category_id`, `name`, `description`, `meta_description`, `meta_keyword`, `language_id`) SELECT `category_id`, `name`, `description`, `meta_description`, `meta_keyword`, @languageid FROM `oc_category_description` WHERE `language_id` = @englishlanguageid;
INSERT IGNORE INTO `oc_product_description`(`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`, `tag`) SELECT `product_id`, @languageid, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`, `tag` FROM `oc_product_description` WHERE `language_id` = @englishlanguageid;
INSERT IGNORE INTO `oc_product_attribute`(`product_id`, `attribute_id`, `language_id`, `text`) SELECT `product_id`, `attribute_id`, @languageid, `text` FROM `oc_product_attribute` WHERE `language_id` = @englishlanguageid;
INSERT IGNORE INTO `oc_attribute_description`(`attribute_id`, `name`, `language_id`) SELECT `attribute_id`, `name`, @languageid FROM `oc_attribute_description` WHERE language_id = @englishlanguageid;
INSERT IGNORE INTO `oc_attribute_group_description`(`attribute_group_id`, `name`, `language_id`) SELECT `attribute_group_id`, `name`, @languageid FROM `oc_attribute_group_description` WHERE `language_id` = @englishlanguageid;
INSERT IGNORE INTO `oc_banner_image`(`banner_id`, `language_id`, `title`, `link`, `image`, `sort_order`) SELECT `banner_id`, @languageid, `title`, `link`, `image`, `sort_order` FROM `oc_banner_image` WHERE `language_id` = @englishlanguageid;


-- translating categories descriptions *DO NOT ADD to extra*
UPDATE IGNORE `oc_category_description` SET `name` = 'Schermi', `description` =  'Schermi per computer'  WHERE `language_id` = @languageid AND `name` = 'Monitors' AND `description` = '' AND `category_id` = '28';
UPDATE IGNORE `oc_category_description` SET `name` = 'Scanner', `description` =  'Scanner per ottenere il massimo dai propri documenti in minor tempo possibile'  WHERE `language_id` = @languageid AND `name` = 'Scanners' AND `description` = '' AND `category_id` = '31';
UPDATE IGNORE `oc_category_description` SET `name` = 'Componenti', `description` =  'Il meglio dei componenti per Computer' WHERE `language_id` = @languageid AND `name` = 'Components' AND `description` = '' AND `category_id` = '25';
UPDATE IGNORE `oc_category_description` SET `name` = 'Telefoni e Smartphone', `description` =  'La telefonia ai prezzi migliori sul mercato' WHERE `language_id` = @languageid AND `name` = 'Phones &amp; PDAs' AND `description` = '' AND `category_id` = '24';
UPDATE IGNORE `oc_category_description` SET `name` = 'Lettori MP3', `description` =  'I lettori mp3 ai prezzi migliori sul mercato' WHERE `language_id` = @languageid AND `name` = 'MP3 Players' AND `category_id` = '34';
UPDATE IGNORE `oc_category_description` SET `name` = 'Portatili', `description` =  'I portatili ai prezzi migliori sul mercato' WHERE `language_id` = @languageid AND `name` = 'Laptops &amp; Notebooks' AND `category_id` = '18';
UPDATE IGNORE `oc_category_description` SET `name` = 'Tablet', `description` =  'Tablet di ultima generazione' WHERE `language_id` = @languageid AND `name` = 'Tablets' AND `description` = '' AND `category_id` = '57';
UPDATE IGNORE `oc_category_description` SET `name` = 'Macchine Fotografiche', `description` =  'Macchine fotografiche di ultima generazione' WHERE `language_id` = @languageid AND `name` = 'Cameras' AND `description` = '' AND `category_id` = '33';
UPDATE IGNORE `oc_category_description` SET `name` = 'WebCam', `description` =  'Webcam per le tue videochiamate'  WHERE `language_id` = @languageid AND `name` = 'Web Cameras' AND `description` = '' AND `category_id` = '32';
UPDATE IGNORE `oc_category_description` SET `name` = 'Stampanti', `description` =  'I prodotti migliori per la stampa.'  WHERE `language_id` = @languageid AND `name` = 'Printers' AND `description` = '' AND `category_id` = '30';
UPDATE IGNORE `oc_category_description` SET `name` = 'Mouse', `description` =  'Mouse per ogni esigenza.'  WHERE `language_id` = @languageid AND `name` = 'Mice and Trackballs' AND `description` = '' AND `category_id` = '29';
UPDATE IGNORE `oc_category_description` SET `name` = 'PC da Tavolo', `description` =  'I PC da tavolo per qualsiasi esigenza' , `meta_description` = 'Computer Desktop' WHERE `language_id` = @languageid AND `name` = 'Desktops' AND `category_id` = '20';

-- translating products descriptions *DO NOT ADD to extra*
UPDATE IGNORE `oc_product_description` SET `name` = 'HTC Touch HD', `description` =  '&lt;p&gt;HTC Touch - in alta definizione. Guarda video musicali e contenuti in streaming con una nitidezza ad alta definizione impressionante per un&apos;esperienza mobile che non avresti mai pensato possibile. Seducentemente elegante, l&apos;HTC Touch HD offre la prossima generazione di funzionalità mobili, il tutto con un semplice tocco. Completamente integrato con Windows Mobile Professional 6.1, ultraveloce 3.5G, GPS, fotocamera da 5 MP e molto altro ancora, il tutto fornito con una nitidezza mozzafiato da 3,8 & quot; Touchscreen WVGA: puoi assumere il controllo del tuo mondo mobile con HTC Touch HD.&lt;/p&gt;&lt;p&gt;&lt;strong&gt;Funzionalità&lt;/strong&gt;&lt;/p&gt;&lt;ul&gt;	&lt;li&gt;		Processore Qualcomm&amp;reg; MSM 7201A&amp;trade; 528 MHz&lt;/li&gt;	&lt;li&gt;		Sistema OperativoWindows Mobile&amp;reg; 6.1 Professional&lt;/li&gt;	&lt;li&gt;		Memoria: 512 MB ROM, 288 MB RAM&lt;/li&gt;	&lt;li&gt;		Dimensioni: 115 mm x 62.8 mm x 12 mm / 146.4 grammi&lt;/li&gt;	&lt;li&gt;		Schermo da 3.8-pollici TFT-LCD touch con risoluzione 480 x 800 WVGA&lt;/li&gt;	&lt;li&gt;		HSDPA/WCDMA: Europa/Asia: 900/2100 MHz; Fino a 2 Mbps up-link e 7.2 Mbps down-link&lt;/li&gt;	&lt;li&gt;		Quad-band GSM/GPRS/EDGE: Europa/Asia: 850/900/1800/1900 MHz (Frequenza di banda, disponibilità HSUPA e velocità dipendono dagli operatori.)&lt;/li&gt;	&lt;li&gt;		Controllo dispositivo tramite HTC TouchFLO&amp;trade; 3D &amp;amp; Pulsanti Pannello frontale sensibile al tocco&lt;/li&gt;	&lt;li&gt;		GPS e A-GPS ready&lt;/li&gt;	&lt;li&gt;		Bluetooth&amp;reg; 2.0 con Trasferimento avanzato dati e A2DP per headsets  wireless stereo&lt;/li&gt;	&lt;li&gt;		Wi-Fi&amp;reg;: IEEE 802.11 b/g&lt;/li&gt;	&lt;li&gt;		HTC ExtUSB&amp;trade; (11-pin mini-USB 2.0)&lt;/li&gt;	&lt;li&gt;		Fotocamera da 5 megapixel con auto focus&lt;/li&gt;	&lt;li&gt;		Fotocamera VGA CMOS&lt;/li&gt;	&lt;li&gt;		Jack audio da 3.5 mm integrato, microfono, cassa e radio FM&lt;/li&gt;	&lt;li&gt;		Formati Suonerie: AAC, AAC+, eAAC+, AMR-NB, AMR-WB, QCP, MP3, WMA, WAV&lt;/li&gt;	&lt;li&gt;		40 polifoniche e  standard MIDI in formato 0 e 1 (SMF)/SP MIDI&lt;/li&gt;	&lt;li&gt;		Batteria al  Lithium-ion o polimeri di Lithium-ion da 1350 mAh&lt;/li&gt;	&lt;li&gt;		Slot di espansione: scheda di memoria microSD&amp;trade; (SD 2.0 compatibile)&lt;/li&gt;	&lt;li&gt;		Alimentatore AC con frequenze: 100 ~ 240V AC, 50/60 Hz DC output: 5V e 1A&lt;/li&gt;	&lt;li&gt;		Funzioni Speciali: FM Radio, G-Sensor&lt;/li&gt;&lt;/ul&gt;', `meta_title` = 'HTC Touch HD', `meta_description` = 'HTC Touch HD' WHERE `language_id` = @languageid AND `name` = 'HTC Touch HD' AND `product_id` = 28;
UPDATE IGNORE `oc_product_description` SET `name` = 'Palm Treo Pro', `description` =  '&lt;p&gt;Ridefinisci la tua giornata lavorativa con lo smartphone Palm Treo Pro. Perfettamente bilanciato, puoi rispondere alle e-mail aziendali e personali, rimanere aggiornato su appuntamenti e contatti e utilizzare Wi-Fi o GPS quando sei in giro. Quindi guarda un video su YouTube, rimani aggiornato su notizie e sport sul Web o ascolta alcune canzoni. Bilancia il tuo lavoro e gioca come preferisci, con Palm Treo Pro.&lt;br&gt;&lt;br&gt;&lt;b&gt;Caratteristiche&lt;/b&gt;&lt;br&gt;&lt;/p&gt;&lt;ul&gt;&lt;li&gt;Windows Mobile&reg; 6.1 Professional Edition&lt;br&gt;&lt;/li&gt;&lt;li&gt;Processore Qualcomm&reg; MSM7201 da 400 MHz&lt;/li&gt;&lt;li&gt;Touchscreen TFT a colori transflettivo 320x320&lt;/li&gt;&lt;li&gt;Radio HSDPA / UMTS / EDGE / GPRS / GSM&lt;/li&gt;&lt;li&gt;UMTS tri-band: 850 MHz, 1900 MHz, 2100 MHz&lt;/li&gt;&lt;li&gt;GSM quad-band - 850/900/1800/1900&lt;/li&gt;&lt;li&gt;802.11b / g con autenticazione WPA, WPA2 e 801.1x&lt;/li&gt;&lt;li&gt;GPS integrato&lt;/li&gt;&lt;li&gt;Versione Bluetooth: 2.0 + velocit&agrave; dati avanzata&lt;/li&gt;&lt;li&gt;256 MB di spazio di archiviazione (100 MB disponibili per l&apos;utente), 128 MB di RAM&lt;/li&gt;&lt;li&gt;Fotocamera da 2.0 megapixel, zoom digitale fino a 8x e acquisizione video&lt;br&gt;&lt;/li&gt;&lt;li&gt;Batteria agli ioni di litio da 1500 mAh rimovibile e ricaricabile&lt;/li&gt;&lt;li&gt;Fino a 5,0 ore di conversazione e fino a 250 ore in standby&lt;/li&gt;&lt;li&gt;Espansione della scheda MicroSDHC (fino a 32 GB supportati)&lt;/li&gt;&lt;li&gt;MicroUSB 2.0 per sincronizzazione e ricarica&lt;/li&gt;&lt;li&gt;Jack per cuffie stereo da 3,5 mm&lt;/li&gt;&lt;li&gt;60 mm (L) x 114 mm (L) x 13,5 mm (P) / 133 g&lt;br&gt;&lt;/li&gt;&lt;/ul&gt;', `meta_title` = 'Palm Treo Pro', `meta_description` = 'Palm Treo Pro' WHERE `language_id` = @languageid AND `name` = 'Palm Treo Pro' AND `product_id` = 29;
UPDATE IGNORE `oc_product_description` SET `name` = 'Canon EOS 5D', `description` =  'Il materiale per la stampa di Canon per EOS 5D afferma che "definisce (una) nuova categoria di reflex digitali", sebbene in genere non siamo troppo interessati ai discorsi di marketing, questa particolare affermazione è chiaramente piuttosto accurata. La EOS 5D è diversa da qualsiasi reflex digitale precedente in quanto combina un sensore full frame (35 mm) ad alta risoluzione (12,8 megapixel) con un corpo relativamente compatto (leggermente più grande della EOS 20D, anche se nella tua mano si sente notevolmente più grande). EOS 5D ha lo scopo di inserirsi tra la EOS 20D e le reflex digitali professionali EOS-1D, una differenza importante rispetto a quest&apos;ultima è che EOS 5D non ha alcun sigillo ambientale. Sebbene Canon non si riferisca specificamente a EOS 5D come a una reflex digitale "professionale", avrà un evidente fascino per i professionisti che desiderano una reflex digitale di alta qualità in un corpo più leggero di EOS-1D. Sicuramente piacerà anche agli attuali possessori di EOS 20D (anche se speriamo che non abbiano acquistato troppi obiettivi EF-S ...)', `meta_title` = 'Canon EOS 5D', `meta_description` = 'Canon EOS 5D' WHERE `language_id` = @languageid AND `name` = 'Canon EOS 5D' AND `product_id` = 30;
UPDATE IGNORE `oc_product_description` SET `name` = 'Nikon D300', `description` =  '&lt;div class=&quot;cpt_product_description &quot;&gt;Progettata con funzionalit&agrave; e prestazioni di livello professionale, la D300 da 12,3 megapixel effettivi combina nuove tecnologie con funzionalit&agrave; avanzate ereditate dalla fotocamera SLR digitale professionale D3 di recente annunciata da Nikon per offrire ai fotografi seri prestazioni straordinarie combinate con l&apos;agilit&agrave;.&lt;br&gt;&lt;br&gt;Simile alla D3, la D300 &egrave; dotata dell&apos;esclusivo sistema di elaborazione delle immagini EXPEED di Nikon, fondamentale per aumentare la velocit&agrave; e la potenza di elaborazione necessarie per molte delle nuove funzionalit&agrave; della fotocamera. La D300 &egrave; dotata di un nuovo sistema di messa a fuoco automatica a 51 punti con la funzione 3D Focus Tracking di Nikon e due nuove modalit&agrave; di scatto LiveView che consentono agli utenti di inquadrare una fotografia utilizzando il monitor LCD ad alta risoluzione della fotocamera. La D300 condivide un sistema di riconoscimento scena simile a quello della D3; promette di migliorare notevolmente la precisione dell&apos;autofocus, dell&apos;esposizione automatica e del bilanciamento del bianco automatico riconoscendo il soggetto o la scena da fotografare e applicando queste informazioni ai calcoli per le tre funzioni.&lt;br&gt;&lt;br&gt;La D300 reagisce alla velocit&agrave; della luce, accendendosi in soli 0,13 secondi e scattando con un impercettibile tempo di ritardo allo scatto di 45 millisecondi. La D300 &egrave; in grado di scattare a una velocit&agrave; di sei fotogrammi al secondo e pu&ograve; raggiungere una velocit&agrave; di otto fotogrammi al secondo quando si utilizza la batteria multi-alimentazione MB-D10 opzionale. In raffiche continue, la D300 pu&ograve; scattare fino a 100 scatti alla massima risoluzione di 12,3 megapixel. (Impostazione dell&apos;immagine NORMALE-GRANDE, utilizzando una scheda CompactFlash SanDisk Extreme IV da 1 GB.)&lt;br&gt;&lt;br&gt;La D300 incorpora una gamma di tecnologie e funzionalit&agrave; innovative che miglioreranno in modo significativo l&apos;accuratezza, il controllo e le prestazioni che i fotografi possono ottenere dalla loro attrezzatura. Il suo nuovo sistema di riconoscimento scena fa avanzare l&apos;uso dell&apos;acclamato sensore Nikon a 1.005 segmenti per riconoscere i colori e gli schemi di luce che aiutano la fotocamera a determinare il soggetto e il tipo di scena da fotografare prima di scattare una foto. Queste informazioni vengono utilizzate per migliorare la precisione delle funzioni di messa a fuoco automatica, esposizione automatica e bilanciamento del bianco automatico nella D300. Ad esempio, la fotocamera pu&ograve; seguire meglio i soggetti in movimento e, identificandoli, pu&ograve; anche selezionare automaticamente i punti AF pi&ugrave; velocemente e con maggiore precisione. Pu&ograve; anche analizzare le alte luci e determinare in modo pi&ugrave; accurato l&apos;esposizione, nonch&eacute; dedurre le sorgenti luminose per fornire un rilevamento del bilanciamento del bianco pi&ugrave; accurato.&lt;/div&gt;', `meta_title` = 'Nikon D300', `meta_description` = 'Nikon D300' WHERE `language_id` = @languageid AND `name` = 'Nikon D300' AND `product_id` = 31;
UPDATE IGNORE `oc_product_description` SET `name` = 'iPod Touch', `description` =  '&lt;p&gt;&lt;b&gt;Rivoluzionaria interfaccia multi-touch.&lt;/b&gt;&lt;br&gt;iPod touch presenta la stessa tecnologia dello schermo multi-touch di iPhone. Pizzica per ingrandire una foto. Scorri le tue canzoni e i tuoi video con un tocco. Sfoglia la tua libreria in base alle copertine degli album con Cover Flow.&lt;br&gt;&lt;b&gt;&lt;br&gt;Splendido display widescreen da 3,5 pollici.&lt;/b&gt;&lt;br&gt;Guarda i tuoi film, programmi TV e foto prendere vita con colori brillanti e vivaci sul display da 320 x 480 pixel.&lt;br&gt;&lt;br&gt;&lt;b&gt;Download di musica direttamente da iTunes.&lt;/b&gt;&lt;br&gt;Acquista su iTunes Wi-Fi Music Store da qualsiasi luogo con Wi-Fi.1 Sfoglia o cerca per trovare la musica che stai cercando, ascoltala in anteprima e acquistala con un semplice tocco.&lt;br&gt;&lt;br&gt;&lt;b&gt;Naviga sul Web con il Wi-Fi.&lt;/b&gt;&lt;br&gt;Naviga sul Web utilizzando Safari e guarda i video di YouTube sul primo iPod con Wi-Fi integrato&lt;/p&gt;', `meta_title` = 'iPod Touch', `meta_description` = 'iPod Touch' WHERE `language_id` = @languageid AND `name` = 'iPod Touch' AND `product_id` = 32;
UPDATE IGNORE `oc_product_description` SET `name` = 'Samsung SyncMaster 941BW', `description` =  'Immagina i vantaggi di andare alla grande senza rallentare. Il grande monitor 941BW da 19 "combina proporzioni ampie con tempi di risposta dei pixel rapidi, per immagini più grandi, più spazio per lavorare e movimenti nitidi. Inoltre, le esclusive tecnologie MagicBright 2, MagicColor e MagicTune aiutano a fornire l&apos;immagine ideale in ogni situazione, mentre Le cornici sottili e sottili e i supporti regolabili offrono lo stile che desideri Con il monitor LCD analogico / digitale widescreen 941BW Samsung, non è difficile da immaginare.', `meta_title` = 'Samsung SyncMaster 941BW', `meta_description` = 'Samsung SyncMaster 941BW' WHERE `language_id` = @languageid AND `name` = 'Samsung SyncMaster 941BW' AND `product_id` = 33;
UPDATE IGNORE `oc_product_description` SET `name` = 'iPod Shuffle', `description` =  '&lt;div&gt;&lt;b&gt;Nato per essere indossato.&lt;/b&gt;&lt;br&gt;Aggancia il lettore musicale pi&ugrave; indossabile al mondo e porta con te fino a 240 brani ovunque. Scegli tra cinque colori, tra cui quattro nuove tonalit&agrave;, per rendere la tua dichiarazione di moda musicale.&lt;br&gt;&lt;br&gt;&lt;b&gt;Il casual incontra il ritmo.&lt;/b&gt;&lt;br&gt;Con il riempimento automatico di iTunes, iPod shuffle pu&ograve; offrire una nuova esperienza musicale ogni volta che esegui la sincronizzazione. Per una maggiore casualit&agrave;, puoi riprodurre in ordine casuale i brani durante la riproduzione con lo scorrimento di un interruttore.&lt;/div&gt;&lt;div&gt;&lt;br&gt;&lt;b&gt;Tutto &egrave; facile.&lt;/b&gt;&lt;br&gt;Carica e sincronizza con il dock USB incluso. Utilizza i controlli dell&apos;iPod shuffle con una mano. Goditi fino a 12 ore consecutive di riproduzione musicale senza interruzioni.&lt;/div&gt;', `meta_title` = 'iPod Shuffle', `meta_description` = 'iPod Shuffle' WHERE `language_id` = @languageid AND `name` = 'iPod Shuffle' AND `product_id` = 34;
UPDATE IGNORE `oc_product_description` SET `name` = 'Prodotto 8', `description` =  '&lt;p&gt;Prodotto 8&lt;/p&gt;', `meta_title` = 'Prodotto 8', `meta_description` = 'Prodotto 8' WHERE `language_id` = @languageid AND `name` = 'Product 8' AND `product_id` = 35;
UPDATE IGNORE `oc_product_description` SET `name` = 'iPod Nano', `description` =  '&lt;div&gt;&lt;b&gt;Video in tasca.&lt;br&gt;&lt;br&gt;&lt;/b&gt;&lt;/div&gt;&lt;div&gt;&Egrave; il piccolo iPod con un&apos;idea molto grande: il video. Il lettore musicale pi&ugrave; popolare al mondo ora ti consente di goderti film, programmi TV e altro su un display da due pollici che &egrave; il 65% pi&ugrave; luminoso di prima.&lt;br&gt;&lt;br&gt;&lt;b&gt;Flusso di copertura.&lt;br&gt;&lt;/b&gt;&lt;br&gt;Sfoglia la tua raccolta musicale sfogliando le copertine degli album. Seleziona un album per capovolgerlo e visualizzare l&apos;elenco delle tracce.&lt;br&gt;&lt;br&gt;&lt;b&gt;Interfaccia migliorata.&lt;br&gt;&lt;/b&gt;&lt;br&gt;Scopri un modo completamente nuovo di sfogliare e visualizzare la tua musica e i tuoi video.&lt;br&gt;&lt;br&gt;&lt;b&gt;Elegante e colorato.&lt;br&gt;&lt;/b&gt;&lt;br&gt;Con un involucro in alluminio anodizzato e acciaio inossidabile lucido e una scelta di cinque colori, iPod nano &egrave; vestito per stupire.&lt;br&gt;&lt;br&gt;&lt;b&gt;iTunes.&lt;/b&gt;&lt;/div&gt;&lt;div&gt;&lt;br&gt;Disponibile come download gratuito, iTunes semplifica la ricerca e l&apos;acquisto di milioni di brani, film, programmi TV, audiolibri e giochi e il download di podcast gratuiti su iTunes Store. E puoi importare la tua musica, gestire l&apos;intera libreria multimediale e sincronizzare facilmente il tuo iPod o iPhone.&lt;/div&gt;', `meta_title` = 'iPod Nano', `meta_description` = 'iPod Nano' WHERE `language_id` = @languageid AND `name` = 'iPod Nano' AND `product_id` = 36;
UPDATE IGNORE `oc_product_description` SET `name` = 'iPhone', `description` =  'iPhone è un nuovo telefono cellulare rivoluzionario che ti consente di effettuare una chiamata semplicemente toccando un nome o un numero nella tua rubrica, un elenco dei preferiti o un registro delle chiamate. Inoltre sincronizza automaticamente tutti i tuoi contatti da un PC, Mac o servizio Internet. E ti consente di selezionare e ascoltare i messaggi di posta vocale nell&apos;ordine che preferisci, proprio come la posta elettronica.', `meta_title` = 'iPhone', `meta_description` = 'iPhone' WHERE `language_id` = @languageid AND `name` = 'iPhone' AND `product_id` = 40;
UPDATE IGNORE `oc_product_description` SET `name` = 'iMac', `description` =  'Proprio quando pensavi che iMac avesse tutto, ora ce n&apos;è ancora di più. Processori Intel Core 2 Duo più potenti. E più standard di memoria. Combinalo con Mac OS X Leopard e iLife ´08, ed è più all-in-one che mai. iMac racchiude prestazioni straordinarie in uno spazio incredibilmente sottile.', `meta_title` = 'iMac', `meta_description` = 'iMac' WHERE `language_id` = @languageid AND `name` = 'iMac' AND `product_id` = 41;
UPDATE IGNORE `oc_product_description` SET `name` = 'Apple Cinema 30&quot;', `description` =  '&lt;p&gt;L&apos;Apple Cinema HD Display da 30 pollici offre una straordinaria risoluzione di 2560 x 1600 pixel. Progettato specificamente per il professionista creativo, questo display offre pi&ugrave; spazio per un accesso pi&ugrave; facile a tutti gli strumenti e le tavolozze necessari per modificare, formattare e comporre il tuo lavoro. Combina questo display con un Mac Pro, MacBook Pro o PowerMac G5 e non c&apos;&egrave; limite a ci&ograve; che puoi ottenere.&lt;br&gt;&lt;br&gt;Cinema HD &egrave; dotato di un display a cristalli liquidi a matrice attiva che produce immagini prive di sfarfallio che offrono il doppio della luminosit&agrave;, il doppio della nitidezza e il doppio del rapporto di contrasto di un tipico display CRT. A differenza di altri schermi piatti, &egrave; progettato con un&apos;interfaccia digitale pura per fornire immagini prive di distorsioni che non necessitano di regolazione. Con oltre 4 milioni di pixel digitali, il display &egrave; particolarmente adatto per applicazioni scientifiche e tecniche come la visualizzazione di strutture molecolari o l&apos;analisi di dati geologici.&lt;br&gt;&lt;br&gt;Offrendo prestazioni cromatiche accurate e brillanti, Cinema HD offre fino a 16,7 milioni di colori su un&apos;ampia gamma, consentendo di vedere sottili sfumature tra i colori, dai tenui pastelli ai ricchi toni gioiello. Un ampio angolo di visione garantisce colori uniformi da bordo a bordo. La tecnologia ColorSync di Apple consente di creare profili personalizzati per mantenere colori uniformi sullo schermo e in stampa. Il risultato: puoi usare con sicurezza questo display in tutte le tue applicazioni in cui il colore &egrave; critico.&lt;br&gt;&lt;br&gt;Ospitato in un nuovo design in alluminio, il display ha una cornice molto sottile che migliora la precisione visiva. Ogni display &egrave; dotato di due porte FireWire 400 e due porte USB 2.0, rendendo il collegamento di periferiche desktop, come iSight, iPod, fotocamere digitali e fisse, dischi rigidi, stampanti e scanner, ancora pi&ugrave; accessibile e conveniente. Sfruttando l&apos;ingombro molto pi&ugrave; sottile e leggero di un LCD, i nuovi display supportano lo standard di interfaccia di montaggio VESA (Video Electronics Standards Association). I clienti con il kit adattatore di montaggio VESA Cinema Display opzionale ottengono la flessibilit&agrave; di montare il display nelle posizioni pi&ugrave; appropriate per il loro ambiente di lavoro.&lt;br&gt;&lt;br&gt;Cinema HD presenta un design a cavo singolo con elegante breakout per USB 2.0, FireWire 400 e una connessione digitale pura che utilizza l&apos;interfaccia DVI (Digital Video Interface) standard del settore. La connessione DVI consente una connessione digitale pura diretta.&lt;/p&gt;', `meta_title` = 'Apple Cinema 30&quot;', `meta_description` = 'Apple Cinema 30&quot;' WHERE `language_id` = @languageid AND `name` = 'Apple Cinema 30&quot;' AND `product_id` = 42;
UPDATE IGNORE `oc_product_description` SET `name` = 'MacBook', `description` =  '&lt;div&gt;&lt;b&gt;Processore Intel Core 2 Duo&lt;/b&gt;&lt;br&gt;&lt;br&gt;Alimentato da un processore Intel Core 2 Duo a velocit&agrave; fino a 2,16 GHz, il nuovo MacBook &egrave; il pi&ugrave; veloce in assoluto.&lt;br&gt;&lt;br&gt;&lt;b&gt;1 GB di memoria, dischi rigidi pi&ugrave; grandi&lt;/b&gt;&lt;br&gt;&lt;br&gt;Il nuovo MacBook ora viene fornito con 1 GB di memoria standard e dischi rigidi pi&ugrave; grandi per l&apos;intera linea, perfetti per eseguire pi&ugrave; applicazioni preferite e archiviare raccolte multimediali in crescita.&lt;br&gt;&lt;br&gt;&lt;b&gt;Design elegante e sottile da 1,08 pollici&lt;/b&gt;&lt;br&gt;&lt;br&gt;MacBook rende facile mettersi in viaggio grazie alla sua robusta custodia in policarbonato, alle tecnologie wireless integrate e all&apos;innovativo alimentatore MagSafe che si sblocca automaticamente se qualcuno inciampa accidentalmente sul cavo.&lt;br&gt;&lt;br&gt;&lt;b&gt;Fotocamera iSight integrata&lt;br&gt;&lt;br&gt;&lt;/b&gt;Immediatamente fuori dagli schemi, puoi chattare video con amici o familiari, 2 registrare un video alla scrivania o scattare foto divertenti con Photo Booth&lt;/div&gt;', `meta_title` = 'MacBook', `meta_description` = 'MacBook' WHERE `language_id` = @languageid AND `name` = 'MacBook' AND `product_id` = 43;
UPDATE IGNORE `oc_product_description` SET `name` = 'MacBook Air', `description` =  'MacBook Air è ultrasottile, ultraportatile e ultra diverso da qualsiasi altra cosa. Ma non perdi pollici e libbre dall&apos;oggi al domani. È il risultato di un ripensamento delle convenzioni. Di molteplici innovazioni wireless. E dal design innovativo. Con MacBook Air, il mobile computing ha improvvisamente un nuovo standard.', `meta_title` = 'MacBook Air', `meta_description` = 'MacBook Air' WHERE `language_id` = @languageid AND `name` = 'MacBook Air' AND `product_id` = 44;
UPDATE IGNORE `oc_product_description` SET `name` = 'MacBook Pro', `description` =  '&lt;div class=&quot;cpt_product_description &quot;&gt;&lt;b&gt;Ultima architettura mobile Intel&lt;/b&gt;&lt;br&gt;&lt;br&gt;Alimentato dai pi&ugrave; avanzati processori mobili di Intel, il nuovo Core 2 Duo MacBook Pro &egrave; oltre il 50% pi&ugrave; veloce del Core Duo MacBook Pro originale e ora supporta fino a 4 GB di RAM.&lt;br&gt;&lt;br&gt;&lt;b&gt;Grafica all&apos;avanguardia&lt;/b&gt;&lt;br&gt;&lt;br&gt;La NVIDIA GeForce 8600M GT offre un&apos;eccezionale potenza di elaborazione grafica. Per la migliore tela creativa, puoi persino configurare il modello da 17 pollici con un display con risoluzione 1920 x 1200.&lt;br&gt;&lt;br&gt;&lt;b&gt;Progettato per la vita in viaggio&lt;/b&gt;&lt;br&gt;&lt;br&gt;Innovazioni come una connessione di alimentazione magnetica e una tastiera illuminata con sensore di luce ambientale mettono il MacBook Pro in una classe a s&eacute;.&lt;br&gt;&lt;br&gt;&lt;b&gt;Collegare. Creare. Comunicare.&lt;/b&gt;&lt;br&gt;&lt;br&gt;Imposta rapidamente una videoconferenza con la videocamera iSight integrata. Controlla presentazioni e contenuti multimediali fino a 9 metri di distanza con l&apos;Apple Remote incluso. Collegati a periferiche a larghezza di banda elevata con FireWire 800 e DVI.&lt;br&gt;&lt;br&gt;&lt;b&gt;Wireless di nuova generazione&lt;/b&gt;&lt;br&gt;&lt;br&gt;Dotato della tecnologia wireless 802.11n, il MacBook Pro offre prestazioni fino a cinque volte superiori e fino al doppio della gamma delle tecnologie della generazione precedente.&lt;/div&gt;', `meta_title` = 'MacBook Pro', `meta_description` = 'MacBook Pro' WHERE `language_id` = @languageid AND `name` = 'MacBook Pro' AND `product_id` = 45;
UPDATE IGNORE `oc_product_description` SET `name` = 'Sony VAIO', `description` =  'Potenza senza precedenti. La prossima generazione di tecnologia di elaborazione è arrivata. Nei notebook VAIO più recenti si trova l&apos;ultima e più potente innovazione di Intel: la tecnologia del processore Intel® Centrino® 2. Vantando un&apos;incredibile velocità, una connettività wireless estesa, un supporto multimediale avanzato e una maggiore efficienza energetica, tutti gli elementi essenziali ad alte prestazioni sono perfettamente combinati in un unico chip.', `meta_title` = 'Sony VAIO', `meta_description` = 'Sony VAIO' WHERE `language_id` = @languageid AND `name` = 'Sony VAIO' AND `product_id` = 46;
UPDATE IGNORE `oc_product_description` SET `name` = 'HP LP3065', `description` =  'Impressiona i tuoi colleghi con il nuovo straordinario monitor a schermo piatto HP LP3065 con diagonale da 30 pollici. Questo monitor di punta offre le migliori prestazioni e funzionalità di presentazione. Ti consente di lavorare nel modo più confortevole possibile tanto da poterti far dimenticare di essere in ufficio', `meta_title` = 'HP LP3065', `meta_description` = 'HP LP3065' WHERE `language_id` = @languageid AND `name` = 'HP LP3065' AND `product_id` = 47;
UPDATE IGNORE `oc_product_description` SET `name` = 'iPod Classic', `description` =  '&lt;div class=&quot;cpt_product_description &quot;&gt;	&lt;div&gt;		&lt;p&gt;			&lt;strong&gt;Pi&ugrave; spazio per muoversi.&lt;/strong&gt;&lt;/p&gt;		&lt;p&gt;Con 80 GB o 160 GB di spazio di archiviazione e fino a 40 ore di durata della batteria, il nuovo iPod classic ti consente di ascoltare fino a 40.000 brani o fino a 200 ore di video o qualsiasi combinazione, ovunque tu vada.&lt;/p&gt;		&lt;p&gt;&lt;b&gt;Elenco Copertine.&lt;/b&gt;&lt;/p&gt;		&lt;p&gt;			Sfoglia la tua raccolta musicale con le copertine degli album. Seleziona un album per&nbsp; visualizzare l&apos;elenco delle tracce.&lt;/p&gt;		&lt;p&gt;			&lt;strong&gt;Interfaccia Avanzata.&lt;/strong&gt;&lt;/p&gt;		&lt;p&gt;Scopri un modo completamente nuovo di sfogliare e visualizzare la tua musica e i tuoi video.&lt;/p&gt;		&lt;p&gt;			&lt;strong&gt;Design pi&ugrave; elegante.&lt;/strong&gt;&lt;/p&gt;		&lt;p&gt;Bello, resistente e pi&ugrave; elegante che mai, iPod classic &egrave; ora dotato di un involucro in alluminio anodizzato e acciaio inossidabile lucido con bordi arrotondati.&lt;/p&gt;	&lt;/div&gt;&lt;/div&gt;&lt;!-- cpt_container_end --&gt;', `meta_title` = 'iPod Classic',  `meta_description` = 'iPod Classic' WHERE `language_id` = @languageid AND `name` = 'iPod Classic' AND `product_id` = 48;
UPDATE IGNORE `oc_product_description` SET `name` = 'Samsung Galaxy Tab 10.1', `description` =  '&lt;p&gt;Samsung Galaxy Tab 10.1, &egrave; il tablet pi&ugrave; sottile al mondo, misura 8,6 mm di spessore, funziona con Android 3.0 Honeycomb OS su un processore Tegra 2 dual-core da 1GHz, simile al fratello minore Samsung Galaxy Tab 8.9.&lt;br&gt;&lt;br&gt;Samsung Galaxy Tab 10.1 offre un&apos;esperienza Android 3.0 pura, aggiungendo il suo nuovo TouchWiz UX o TouchWiz 4.0 - include un pannello live, che ti consente di personalizzare con contenuti diversi, come immagini, segnalibri e feed social, con un capacitivo WXGA da 10,1 pollici touch screen con risoluzione 1280 x 800 pixel, dotato di fotocamera posteriore da 3 megapixel con flash LED e fotocamera frontale da 2 megapixel, connettivit&agrave; HSPA + fino a 21 Mbps, capacit&agrave; di registrazione video HD 720p, riproduzione HD 1080p, supporto DLNA, Bluetooth 2.1, USB 2.0 , giroscopio, Wi-Fi 802.11 a / b / g / n, slot micro-SD, jack per cuffie da 3,5 mm e slot per SIM, incluso il Samsung Stick: un microfono Bluetooth che pu&ograve; essere trasportato in una tasca come una penna e un sound dock con subwoofer amplificato.&lt;br&gt;&lt;br&gt;Samsung Galaxy Tab 10.1 sar&agrave; disponibile in versioni da 16 GB / 32 GB / 64 GB e precaricato con Social Hub, Reader&apos;s Hub, Music Hub e Samsung Mini Apps Tray, che ti d&agrave; accesso alle app pi&ugrave; comunemente utilizzate per facilitare il multitasking ed &egrave; in grado di Adobe Flash Player 10.2, alimentato da una batteria da 6860 mAh che ti offre 10 ore di tempo di riproduzione video.&lt;/p&gt;', `meta_title` = 'Samsung Galaxy Tab 10.1', `meta_description` = 'Samsung Galaxy Tab 10.1' WHERE `language_id` = @languageid AND `name` = 'Samsung Galaxy Tab 10.1' AND `product_id` = 49;


-- extension update - adding the extension to the first user group
UPDATE `oc_user_group` SET `permission` = REPLACE(`permission`, 'user\/user_permission', 'user\/user_permission","extension\/module\/itit_language') WHERE `user_group_id` =  1  AND `permission` NOT LIKE '%extension\/module\/itit_language%';
-- UPDATE `oc_user_group` SET `permission` = REPLACE(`permission`, 'user\/user_permission', 'user\/user_permission","extension\/module\/itit_language') WHERE `user_group_id` =  1  AND `permission` NOT LIKE '%extension\\/module\\/itit_language%';

-- Adding the Extension for our language
INSERT INTO `oc_extension` (`type`, `code`) SELECT * FROM (SELECT 'module', 'itit_language' ) AS tmp WHERE NOT EXISTS ( SELECT * FROM `oc_extension` where `type` =  'module'  AND `code` = 'itit_language' );

-- Adding the settings for the extension if they do not exist
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) SELECT * FROM (SELECT '0' as `tcode`, 'module_itit_language', 'module_itit_language_statuslang', '1' as  `tvalue`, '0' as `tserialized`) AS tmp WHERE NOT EXISTS ( SELECT * FROM `oc_setting` where `code` =  'module_itit_language'  AND `key` = 'module_itit_language_statuslang' );
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) SELECT * FROM (SELECT '0' as `tcode`, 'module_itit_language', 'module_itit_language_version', '0.0.13' as `tvalue`, '0' as `tserialized`) AS tmp WHERE NOT EXISTS ( SELECT * FROM `oc_setting` where `code` =  'module_itit_language'  AND `key` = 'module_itit_language_version' );
INSERT INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) SELECT * FROM (SELECT '0' as `tcode`, 'module_itit_language', 'module_itit_language_status', '1' as `tvalue`, '0' as `tserialized` ) AS tmp WHERE NOT EXISTS ( SELECT * FROM `oc_setting` where `code` =  'module_itit_language'  AND `key` = 'module_itit_language_status' );

-- Adding "Sud Sardegna" Zone - Introduced in 4 february 2016
-- INSERT INTO `oc_zone` (`country_id`, `name`, `code`, `status`) SELECT '105', 'Sud Sardegna', 'SU', '1' WHERE NOT EXISTS (SELECT `zone_id` FROM `oc_zone` where `country_id`='105' AND `code`='SU');
INSERT INTO `oc_zone` (`country_id`, `name`, `code`, `status`) SELECT * FROM (SELECT '105', 'Sud Sardegna', 'SU', '1') AS tmp WHERE NOT EXISTS (SELECT `zone_id` FROM `oc_zone` where `country_id`='105' AND `code`='SU');

-- End of Main Language
NOWDOCEND;
// end of main sql queries

// extra sql queries
$sqlsel[1] = <<<'NOWDOCEND'

-- Language Extra
-- updating the main settings
UPDATE `oc_setting` SET `value` = 'it-it' WHERE `key` = 'config_language';
UPDATE `oc_setting` SET `value` = 'it-it' WHERE `key` = 'config_admin_language';

-- setting the time zone to Europe/Rome IF we still have the default informations - Used only since OC 3.0.3.7 up to OC4.x
UPDATE IGNORE `oc_setting` SET `value` = 'Europe/Rome' WHERE `key` = 'config_timezone' AND `value` = 'UTC' AND EXISTS (SELECT * FROM (SELECT `setting_id` FROM `oc_setting` WHERE `key` = 'config_owner' AND `value` = 'Your Name') TempTable); 

UPDATE `oc_setting` SET `value` = 'Proprietario' WHERE `key` = 'config_owner' AND `value` = 'Your Name';
UPDATE `oc_setting` SET `value` = 'Negozio' WHERE `key` = 'config_name' AND `value` = 'Your Store';
UPDATE `oc_setting` SET `value` = 'Negozio' WHERE `key` = 'config_meta_title' AND `value` = 'Your Store';
UPDATE `oc_setting` SET `value` = 'vendita, prodotti, ecommerce' WHERE `key` = 'config_meta_keyword' AND `value` = '';
UPDATE `oc_setting` SET `value` = 'Negozio' WHERE `key` = 'config_meta_description' AND `value` = 'My Store';
UPDATE `oc_setting` SET `value` = 'Indirizzo azienda' WHERE `key` = 'config_address' AND `value` = 'Address 1'; 
-- Italy = 105 - updating if the default OC value is found
UPDATE `oc_setting` SET `value` = '105' WHERE `key` = 'config_country_id' AND `value` = '222' AND EXISTS ( SELECT * FROM (SELECT `setting_id` FROM `oc_setting` WHERE `key` = 'config_zone_id' AND `value` = '3563') TempTable) ;
-- id of the zone
UPDATE `oc_setting` SET `value` = '3852' WHERE `key` = 'config_zone_id'  AND `value` = '3563';

UPDATE `oc_tax_class` SET `title` = 'Prodotti Tassabili', `description` = 'Prodotti Tassabili' WHERE `title` = 'Taxable Goods' AND `description` = 'Taxed goods';
UPDATE `oc_tax_class` SET `title` = 'Prodotti Scaricabili', `description` = 'Prodotti Scaricabili' WHERE `title` = 'Downloadable Products' AND `description` = 'Downloadable';

-- deleting Default UK taxes from the rules. In most cases they are not needed for italian taxation only
DELETE FROM `oc_tax_rule` WHERE (`tax_class_id` = 9 AND `tax_rate_id` = 86) OR (`tax_class_id` = 10 AND `tax_rate_id` = 86) OR (`tax_class_id` = 9 AND `tax_rate_id` = 87) OR (`tax_class_id` = 10 AND `tax_rate_id` = 87);


UPDATE `oc_layout` SET `name` = 'Pagina Iniziale' WHERE `layout_id` = '1';
UPDATE `oc_layout` SET `name` = 'Prodotti' WHERE `layout_id` = '2';
UPDATE `oc_layout` SET `name` = 'Categorie' WHERE `layout_id` = '3';
UPDATE `oc_layout` SET `name` = 'Predefinito' WHERE `layout_id` = '4';
UPDATE `oc_layout` SET `name` = 'Produttori' WHERE `layout_id` = '5';
UPDATE `oc_layout` SET `name` = 'Account' WHERE `layout_id` = '6';
UPDATE `oc_layout` SET `name` = 'Checkout' WHERE `layout_id` = '7';
UPDATE `oc_layout` SET `name` = 'Contatti' WHERE `layout_id` = '8';
UPDATE `oc_layout` SET `name` = 'Mappa del Sito' WHERE `layout_id` = '9';
UPDATE `oc_layout` SET `name` = 'Affiliati' WHERE `layout_id` = '10';
UPDATE `oc_layout` SET `name` = 'Informazioni' WHERE `layout_id` = '11';
UPDATE `oc_layout` SET `name` = 'Comparazione' WHERE `layout_id` = '12';
UPDATE `oc_layout` SET `name` = 'Ricerca' WHERE `layout_id` = '13';

-- changing the default coupon names
UPDATE IGNORE `oc_coupon` SET `name` = 'Sconto -10%' WHERE `coupon_id` = '4' AND `name` = '-10% Discount';
UPDATE IGNORE `oc_coupon` SET `name` = 'Sconto spedizione gratuita' WHERE `coupon_id` = '5' AND `name` = 'Free Shipping';
UPDATE IGNORE `oc_coupon` SET `name` = 'Sconto -10 &euro;' WHERE coupon_id = '6' AND `name` = '-10.00 Discount';

-- extra stuff that is NOT related to the language


-- Disabling any currency except the euro. In most cases we will use only our own currency if we are selling within italy or europe.
-- Pound, Dollar and Euro are, by default, already available and Enabled.
-- If you are using mysql as reference for your own language remember thay you need
-- to INSERT your currency if it's not EUR, USD or GBP.

UPDATE `oc_currency` SET `status` = '0' WHERE `code` != 'EUR';

-- setting EUR to 1.00000
UPDATE `oc_currency` SET `value` = '1.00000000' WHERE `code` = 'EUR';

-- setting exchange EUR > GBP - 2021-03-10
UPDATE `oc_currency` SET `value` = '0.85600548072432' WHERE `code` = 'GBP';

-- setting exchange EUR > USD - 2021-03-10
UPDATE `oc_currency` SET `value` = '1.1928388574776' WHERE `code` = 'USD';

-- disabling automatic currency - in most cases we will handle 1 currency only and there's no need to update the currencies
UPDATE `oc_setting` SET `value` = '0' WHERE `key` = 'config_currency_auto';

-- setting currency to euro
UPDATE `oc_setting` SET `value` = 'EUR' WHERE `key` = 'config_currency';

-- Affiliates needs approval - in most cases we don't use the affiliation
UPDATE `oc_setting` SET `value` = '1' WHERE `key` = 'config_affiliate_approval';

-- We allow to upload data up to 30MB instead of 300kb
UPDATE `oc_setting` SET `value` = '30000000' WHERE `key` = 'config_file_max_size';

-- do not show weight in the cart
UPDATE `oc_setting` SET `value` = '0' WHERE `key` = 'config_cart_weight';

-- checkout for guests
UPDATE `oc_setting` SET `value` = '0' WHERE `key` = 'config_checkout_guest';

-- replacing the John Doe firstname and lastname with something generic
UPDATE IGNORE `oc_user` SET `firstname` = 'Utente',  `lastname` = 'Admin'  WHERE `firstname` = 'John' AND `lastname` = 'Doe' ;

-- replacing the default names of the user groups
UPDATE IGNORE `oc_user_group` SET `name` = 'Amministratore' WHERE `name` = 'Administrator' AND `user_group_id` = 1;
UPDATE IGNORE `oc_user_group` SET `name` = 'Collaboratore' WHERE `name` = 'Demonstration' AND `user_group_id` = 10;

-- changing the default customer group description to avoid the "test" string for the ENGLISH language
UPDATE IGNORE `oc_customer_group_description` SET `description`= 'Customers' where `description` = 'test' AND `language_id` = @englishlanguageid AND `customer_group_id` = 1;


-- Updating the language installation status for the language Extension
UPDATE `oc_setting` SET `value` = '2' where `code` =  'module_itit_language'  AND `key` = 'module_itit_language_statuslang';


-- End of Language Extra
NOWDOCEND;
// end of extra sql queries

	// delete the previous installation
	$sqlsel[2] = "
SET @languageid = (SELECT language_id FROM `oc_language` WHERE `code` = 'it-it');

DELETE FROM `oc_customer_group_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_information_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_voucher_theme_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_stock_status` WHERE `language_id` = @languageid;
DELETE FROM `oc_return_status` WHERE `language_id` = @languageid;
DELETE FROM `oc_return_reason` WHERE `language_id` = @languageid;
DELETE FROM `oc_return_action` WHERE `language_id` = @languageid;
DELETE FROM `oc_order_status` WHERE `language_id` = @languageid;
DELETE FROM `oc_length_class_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_weight_class_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_option_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_option_value_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_attribute_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_attribute_group_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_category_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_product_attribute` WHERE `language_id` = @languageid;
DELETE FROM `oc_product_description` WHERE `language_id` = @languageid;
DELETE FROM `oc_banner_image` WHERE `language_id` = @languageid;
/*resetting the customer language to english*/
UPDATE IGNORE `oc_customer` SET `language_id` = '1' WHERE `language_id` = @languageid;
DELETE FROM `oc_language` where `code` = 'it-it';


SET @vatgeozoneita = (SELECT `geo_zone_id` FROM `oc_geo_zone` WHERE `name` = 'IVA - Italia');
DELETE FROM `oc_zone_to_geo_zone` WHERE `geo_zone_id` = @vatgeozoneita;
DELETE FROM `oc_tax_rate` WHERE `geo_zone_id` = @vatgeozoneita;
DELETE FROM `oc_geo_zone` WHERE `geo_zone_id` = @vatgeozoneita;

SET @taxrateid = (SELECT `tax_rate_id` FROM `oc_tax_rate` WHERE `name` = 'IVA (22.0%)');
DELETE FROM `oc_tax_rule` WHERE `tax_rate_id` = @taxrateid;
DELETE FROM `oc_tax_rate_to_customer_group` WHERE `tax_rate_id` = @taxrateid;
DELETE FROM `oc_tax_rate` WHERE `tax_rate_id` = @taxrateid;

/*restoring from the Extra*/

/*reverting the settings*/
UPDATE `oc_setting` SET `value` = 'en-gb' WHERE `key` = 'config_language' AND `value` = 'it-it' ;
UPDATE `oc_setting` SET `value` = 'en-gb' WHERE `key` = 'config_admin_language' AND `value` = 'it-it' ;
UPDATE `oc_setting` SET `value` = 'Your Name' WHERE `key` = 'config_owner' AND `value` = 'Proprietario';
UPDATE `oc_setting` SET `value` = 'Your Store' WHERE `key` = 'config_store' AND `value` = 'Negozio';
UPDATE `oc_setting` SET `value` = 'Your Store' WHERE `key` = 'config_name' AND `value` = 'Negozio';
UPDATE `oc_setting` SET `value` = 'Your Store' WHERE `key` = 'config_meta_title' AND `value` = 'Negozio';
UPDATE `oc_setting` SET `value` = 'Your Store' WHERE `key` = 'config_title';
UPDATE `oc_setting` SET `value` = '' WHERE `key` = 'config_meta_keyword' AND `value` = 'vendita, prodotti, ecommerce';
UPDATE `oc_setting` SET `value` = 'Your Store' WHERE `key` = 'config_meta_description' AND `value` = 'Negozio';
UPDATE `oc_setting` SET `value` = 'Address 1' WHERE `key` = 'config_address' AND `value` = 'Indirizzo azienda';

UPDATE `oc_tax_class` SET `title` = 'Taxable Goods', `description` = 'Taxed goods' WHERE `title` = 'Prodotti Tassabili' AND `description` = 'Prodotti Tassabili';
UPDATE `oc_tax_class` SET `title` = 'Downloadable Products', `description` = 'Downloadable' WHERE `title` = 'Prodotti Scaricabili' AND `description` = 'Prodotti Scaricabili';


UPDATE `oc_layout` SET `name` = 'Home' WHERE `name` = 'Pagina Iniziale';
UPDATE `oc_layout` SET `name` = 'Product' WHERE `name` = 'Prodotti';
UPDATE `oc_layout` SET `name` = 'Category' WHERE `name` = 'Categorie';
UPDATE `oc_layout` SET `name` = 'Default' WHERE `name` = 'Predefinito';
UPDATE `oc_layout` SET `name` = 'Manufacturer' WHERE `name` = 'Produttori';
UPDATE `oc_layout` SET `name` = 'Account' WHERE `name` = 'Account';
UPDATE `oc_layout` SET `name` = 'Checkout' WHERE `name` = 'Checkout';
UPDATE `oc_layout` SET `name` = 'Contact' WHERE `name` = 'Contatti';
UPDATE `oc_layout` SET `name` = 'Sitemap' WHERE `name` = 'Mappa del Sito';
UPDATE `oc_layout` SET `name` = 'Affiliate' WHERE `name` = 'Affiliati';
UPDATE `oc_layout` SET `name` = 'Information' WHERE `name` = 'Informazioni';
UPDATE `oc_layout` SET `name` = 'Compare' WHERE `name` = 'Comparazione';
UPDATE `oc_layout` SET `name` = 'Search' WHERE `name` = 'Ricerca';

/*changing the default coupon names */
UPDATE IGNORE `oc_coupon` SET name= '-10% Discount' WHERE name='Discount/Sconto -10%';
UPDATE IGNORE `oc_coupon` SET name= 'Free Shipping' WHERE name='Discount/Sconto free shipping/spedizione gratuita';
UPDATE IGNORE `oc_coupon` SET name= '-10.00 Discount' WHERE name='Discount/Sconto -10 &euro;';

/*Reverting the Utente Admin to John Doe*/
UPDATE IGNORE `oc_user` SET `firstname` = 'John',  `lastname` = 'Doe'  WHERE `firstname` = 'Utente' AND `lastname` = 'Admin' ;

/*reverting the names of the user groups */
UPDATE IGNORE `oc_user_group` SET `name` = 'Administrator' WHERE `name` = 'Amministratore';
UPDATE IGNORE `oc_user_group` SET `name` = 'Demonstration' WHERE `name` = 'Collaboratore';

/*reverting to the  default customer group description*/
UPDATE IGNORE `oc_customer_group_description` SET `description`= 'test' where `description` = 'Customers' AND `language_id` = 1 AND `customer_group_id` = 1;


";


		//removing multiline /**/ comments
		$sqlsel[$selection] = preg_replace('#/\*.*?\*/#s', '', $sqlsel[$selection]);
		//removing -- comments
		$sqlsel[$selection] = preg_replace('#^-- .*#', '', $sqlsel[$selection]);
		//removing empty lines
		$sqlsel[$selection] = preg_replace('#\n\s*\n#', "\n", $sqlsel[$selection]);
		
		//replacing the db_prefix
		$sqlsel[$selection] = str_replace("DROP TABLE IF EXISTS `oc_", "DROP TABLE IF EXISTS `" . DB_PREFIX, $sqlsel[$selection]);
		$sqlsel[$selection] = str_replace("CREATE TABLE `oc_", "CREATE TABLE `" . DB_PREFIX, $sqlsel[$selection]);
		$sqlsel[$selection] = str_replace("INSERT INTO `oc_", "INSERT INTO `" . DB_PREFIX, $sqlsel[$selection]);
		$sqlsel[$selection] = str_replace("INSERT IGNORE INTO `oc_", "INSERT INTO `" . DB_PREFIX, $sqlsel[$selection]);

		//update
		$sqlsel[$selection] = str_replace("UPDATE `oc_", "UPDATE `" . DB_PREFIX, $sqlsel[$selection]);
		$sqlsel[$selection] = str_replace("UPDATE IGNORE `oc_", "UPDATE IGNORE `" . DB_PREFIX, $sqlsel[$selection]);
		//replace for select, delete, etc
		$sqlsel[$selection] = str_replace("FROM `oc_", "FROM `" . DB_PREFIX, $sqlsel[$selection]);
		
		
 // echo $sqlsel[$selection];exit;

		
		//exploding into an array
		$lines = explode("\n", $sqlsel[$selection]);
		
			foreach($lines as $line) {
				if ($line && (substr($line, 0, 2) != '--') && (substr($line, 0, 1) != '#')) {
					$sql .= $line;

					if (preg_match('/;\s*$/', $line)) {

						$this->db->query($sql);

						$sql = '';
					}
				}
			}
			
	return true;

	}
	//end of function sqlinstall

	// Retrieve the current collation of the connection
	public function connectionCollation(): string {
		$query = $this->db->query("SELECT @@collation_connection AS collation");
		return $query->row['collation'];
	}

	public function checkCollation(string $connection_collation = ''): bool {
		// Array of tables and their columns to check
		$tables = [
			'language' => 'code',
			// add more tables and their columns here
		];
	
		$connection_collation = empty($connection_collation) ?: $this->connectionCollation();
	
		foreach ($tables as $table => $column) {
			// adding prefix table 
			$table = DB_PREFIX . $table;
			// Retrieve the collation of the current table
			$table_query = $this->db->query("SHOW TABLE STATUS LIKE '" . $this->db->escape($table) . "'");
			$table_collation = $table_query->row['Collation'];
	
			// Check if column name is empty
			if (!empty($column)) {
				// Retrieve the collation of the specified column in the current table
				$column_query = $this->db->query("SHOW FULL COLUMNS FROM `" . $this->db->escape($table) . "` LIKE '" . $this->db->escape($column) . "'");
				$column_collation = $column_query->row['Collation'];
	
				if ($connection_collation !== $column_collation) {
					return false;
				}
			}
	
			if ($connection_collation !== $table_collation) {
				return false;
			}
		}
	
		return true;
	}

	
	public function editSetting($code, $data, $store_id = 0) {
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				
				$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
				}
			}
		}
	}
	

	public function upgrade($extensionversion) {
		
		switch($extensionversion)
		{ /*previous versions first, latest version at the end -- remove all breaks*/
				case "0.0.1":
				/*end of 0.0.1*/
				
				case "0.0.3":
				
				
				
				/* Only the last break. Do not remove or add another one. */
				break;
		} /*end of case*/

		
	}
	
}