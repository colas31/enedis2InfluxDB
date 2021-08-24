# Enedis2InfluxDB

This code allows to push all history from Enedis to an influxDB instance




## Prerequisite

 - Avoir une instance InfluxDB
 - Installer le plugin scripts
 - Installer le plugin Enedis
 - Autoriser l’accès aux serveurs Enedis : "j’accède à mon espace client
   Enedis"
   

## Setup
 - Dezipper l'archive dans jeedom/plugins/script/data/enedis2InfluxDB
 - Lancer la commande `php composer.phar install`  depuis le folder jeedom/plugins/script/data/enedis2InfluxDB

  

## Configuration

 - Editer le fichier jeedom/plugins/script/data/enedis2influxdb/setup.vars.php
	 - Completer la partie CUSTOM VARS
 - Créer deux scripts:
	 - enedis2influxdb_import
		 - Créer une commande:
			 - Ouvrir le fichier enedis2influxdb/resources/history.php
		 - Le lancer une seule fois manuellement depuis l'option tester de la commande
	 - enedis2influxdb_cron
		 - Mettre une Auto-actualisation (cron): `0 9 * * *`
		 - Créer une commande:
			 - Ouvrir le fichier enedis2influxdb/resources/cron.php