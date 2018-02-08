-- MySQL dump 10.16  Distrib 10.1.29-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: eb_test
-- ------------------------------------------------------
-- Server version	10.1.29-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `consent`
--

DROP TABLE IF EXISTS `consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `consent` (
  `consent_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hashed_user_id` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `service_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `consent_type` varchar(20) COLLATE utf8_unicode_ci DEFAULT 'explicit',
  PRIMARY KEY (`hashed_user_id`,`service_id`),
  KEY `hashed_user_id` (`hashed_user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consent`
--

LOCK TABLES `consent` WRITE;
/*!40000 ALTER TABLE `consent` DISABLE KEYS */;
INSERT INTO `consent` VALUES ('2017-04-18 11:37:00','c37a4b7c8761555297c4d3d4989f6082e64dd812','https://my-test-sp.test','abe55dff15fe253d91220e945cd0f2c5f4727430','explicit');
/*!40000 ALTER TABLE `consent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_changelog`
--

DROP TABLE IF EXISTS `db_changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_changelog` (
  `patch_number` int(11) NOT NULL,
  `branch` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `completed` int(11) DEFAULT NULL,
  `filename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`patch_number`,`branch`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_changelog`
--

LOCK TABLES `db_changelog` WRITE;
/*!40000 ALTER TABLE `db_changelog` DISABLE KEYS */;
INSERT INTO `db_changelog` VALUES (1,'default',1340787301,'patch-0001.php','9e47c3b8','Move the groupProviders configuration from the local config to the database.'),(2,'default',1340787301,'patch-0002.sql','2a482377','Initial migration away from LiquiBase'),(3,'default',1340787301,'patch-0003.sql','271b9ee1','Remove LiquiBase tables'),(4,'default',1340787301,'patch-0004.sql','bece62cf','Table structure for table `emails`'),(5,'default',1340787301,'patch-0005.sql','61a65c9d','Add new emails for deprovisioning'),(6,'default',1340787301,'patch-0006.sql','e7075a42','Update deprovisioning mail'),(7,'default',1340787301,'patch-0007.sql','445ce3bd','Add id field to login table (ported from manage patch-001.sql)'),(8,'default',1340787301,'patch-0008.php','e452a2b0','Add urn:collab:group: prefixing to Grouper group providers'),(9,'default',1340787301,'patch-0009.sql','7fed17ad','Add tables for new persistent NameID'),(10,'default',1340788257,'patch-0010.php','d2a6343a','Assign a UUID to all users in LDAP'),(11,'default',1340788257,'patch-0011.sql','015c7b4a','Add useragent field to login_logs table for logging the User-Agent'),(12,'default',1340788257,'patch-0012.sql','a668c1a3','Add voname field to login_logs table for logging the Virtual Organisation Context'),(13,'default',1340788257,'patch-0013.sql','ab0053d5','Add table for storing the ACL Group provider information for service providers'),(14,'default',1340788257,'patch-0014.sql','e2e35650',''),(15,'default',1340788257,'patch-0015.sql','479a53d4','Add logo_url field to group_provider table for displaying the picture in teams'),(16,'default',1511249619,'patch-0016.php','0d9a91ec','BACKLOG-327: Convert VO configuration, perpend urn:collab:groups:surfteams.nl'),(17,'default',1511249619,'patch-0017.sql','2fda0606','Add keyid field to login_logs table for logging the used keypair. See OpenConext/OpenConext-engineblock#29.'),(18,'default',1511249619,'patch-0018.sql','3c9631d0',''),(19,'default',1511249619,'patch-0019.sql','1bccb625',''),(20,'default',1511249619,'patch-0020.sql','be9c92b4','Drop deprecated emails table'),(21,'default',1511249619,'patch-0021.sql','c852915e','Create sso_provider_roles for PUSH metadata'),(22,'default',1511249619,'patch-0022.sql','66046ae7','Create indexes on sso_provider_roles for PUSH metadata'),(23,'default',1511249619,'patch-0023.sql','3d9128ab','add policy_enforcement_decision_required column to sso_provider_roles'),(24,'default',1511249619,'patch-0024.sql','8886e2dd','Add url_en and url_nl columns to sso_provider_roles table'),(25,'default',1511249619,'patch-0025.sql','03582e42','Add consent_type column to consent table'),(26,'default',1511249619,'patch-0026.sql','616c1f0d','add attribute_aggregation_required column to sso_provider_roles');
/*!40000 ALTER TABLE `db_changelog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('20160307162928'),('20160412141621'),('20160721121856'),('20161123131704'),('20161209145942'),('20161209152354'),('20170331145533'),('20170912155800'),('20180118115853');
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saml_entity`
--

DROP TABLE IF EXISTS `saml_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saml_entity` (
  `saml_entity_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_saml_entity_uuid)',
  `entity_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_entity_id)',
  `entity_type` varchar(20) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_entity_type)',
  `metadata` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_json_metadata)',
  PRIMARY KEY (`saml_entity_uuid`),
  UNIQUE KEY `uniq_saml_entity_entity_id_entity_type` (`entity_id`,`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saml_entity`
--

LOCK TABLES `saml_entity` WRITE;
/*!40000 ALTER TABLE `saml_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `saml_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saml_persistent_id`
--

DROP TABLE IF EXISTS `saml_persistent_id`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saml_persistent_id` (
  `persistent_id` char(40) COLLATE utf8_unicode_ci NOT NULL COMMENT 'SHA1 of service_provider_uuid + user_uuid',
  `user_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `service_provider_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`persistent_id`),
  KEY `user_uuid` (`user_uuid`,`service_provider_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Look up table for persistent_ids we hand out';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saml_persistent_id`
--

LOCK TABLES `saml_persistent_id` WRITE;
/*!40000 ALTER TABLE `saml_persistent_id` DISABLE KEYS */;
INSERT INTO `saml_persistent_id` VALUES ('c5a5348456475369a22bc971160ee827082249da','007802b0-e350-4951-8b65-89bf8a0adc15','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('52574fbdb5adb3d3603f059bc2ad9da8ed16c88a','007802b0-e350-4951-8b65-89bf8a0adc15','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('67a36ff0e96fff4def3b3a3fc2c8d47d2383d889','01b733c2-f7dc-467d-8970-ef956c110b8d','818d6f41-ad23-4925-8e81-4427b848f715'),('89d0cabe504421b3e160e62f9cecda4697a07c5f','01fa2c38-9d99-41de-bc15-adf144946a3c','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('2cd7172303bdc513dcd10b99645b5bf31ccd7a7e','0236d821-7b5d-4bd4-904a-334933a0cf0c','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('320ce81425d18dc10ae2ada99a1eac683a842302','02d294c1-7288-4903-925a-739b8dcc376b','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('11b6b5158694c4e231c5dd2704152b239fe6af6d','0345bba2-1eb0-4751-8c78-95e793910e26','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('932a4d7b796104586ccb88befb217dd442165112','05edf88c-a8c2-4067-ad93-07543a9b026c','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('72dc50c9c3d0b71b3cb8cd1af4134552224c7e4c','071ff3f3-23f1-44f9-a4b7-5710a7ee68b1','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('36341a0e284381bbf9e618618b99a8f64c7df743','087ea6a4-923a-4c9e-86ff-0d62102f363f','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('f4dcd430682bf286bb7a5bbe8cb032a3ca6266dc','0a350222-9f38-4d15-be57-1d9f87c536d7','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('fded44b5af85222291b56040fa3182afe497d003','0dd4d089-9066-4c98-a211-eeb4d69e609f','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('47cf7a8e7e79cc3478e5a8fbb456989f02248dbc','0eb10a7c-3153-45bd-b301-6d6d6a542ce9','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('6ecb97dbc77882dcbc3c1d673145049a2830b036','115a7778-6c2d-4d83-b8a7-e46311804239','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('92b2c78b212829f118dbe51bc884062c5734ba88','119bf55d-fecc-4c58-ba02-3c438090db7a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('d106dd4f7a1aa718268455488eb9b670623aa0c4','11c787ba-458c-4ce8-a170-db09085543f2','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('e41b6dc20d87789068cf7a5fb39731b3e74f71b1','11cefbd1-f72a-462c-a795-232703311b59','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('8288c4fe45da478324fc62dc6a4cc4f13c3c3b5d','133b87c0-d12c-4b62-b6df-06c1ee3752f3','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('89b6930706fa9fc7bc9e9a30ef15da874d3ecac6','13538932-7db3-4e0b-ab49-7737623659a2','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('80abbef882ccbbc7cc59a4e9f2510d538c7abe9c','1444c70d-45db-4270-94b4-c2862b4b7ce5','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('131bb0f4939d05afdde2155ba00cd8ac727f4f97','15010fd1-89a7-44a7-af28-ef6314feedec','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('6a9de98bb9a429916a069ed5165d0a5c83e0e99d','15bc0271-033d-4f4c-beaf-57893af8a85f','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('04327f1b91905b040c0748b3b97391e588ce8823','15bc0271-033d-4f4c-beaf-57893af8a85f','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('50eb899cc2b38251795e76c5c82fe98a7da0db22','1628ee77-1201-431c-9b3f-98a5dee7c2c6','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('44765e5fd7f110ec0bbedb526bf8f242cfb32601','16955c12-9d15-4418-91eb-22e5284e4b0b','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('78798e02525a2b606ffb2d16cc990e58d1f72aa0','1894e17f-6821-444a-ad58-ea5b3d5107d3','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('55e4deaa4ca665adc52754cd34698379ecaead82','196adbd2-b508-43b3-92a0-49ade103df0a','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('d6f899f06d4227591d22dbcf9d919fbbe5808888','19baa8d0-6c31-418d-a87d-3e40a15afe05','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('277fdc3a627cb7bfd351f14b9c8baffa47619539','1a3a8c26-501e-4c87-ad77-197ef4281c28','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('9685dc17cc8676959a02f14c211a1e49bc7571c5','1a3b15f9-8b6d-4c1f-8732-b2ca77d6bc75','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('82a9fd6fbff0030f80cd80b49afd88192e6b87d1','1a565492-2efd-41bc-900f-2067ada2f3d6','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('b4a8fbe02c6d2edfd496df55062dae21c3a3c920','1bbca868-66cf-4074-9a41-85f8180d317d','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('c552fda980a8c55f48bf3fc15231826272266bb3','1c4948ae-1223-4500-800f-bc89a0289fff','818d6f41-ad23-4925-8e81-4427b848f715'),('feee74e8c60bebbe53a79145df919e9742b4646d','1ce4ae27-ca86-473f-b4f8-efedb9541a0e','818d6f41-ad23-4925-8e81-4427b848f715'),('1d3bc21d78616cee78c1ea098f7385f85b4cc1d7','1e896f4f-6087-4288-9f9c-aaf4cec1ec47','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('193d1f0377e414aaa85ebd2cb84b89a038d05612','1fc4b889-e540-4846-a488-c32297faa74c','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('b2b9c21529fc5411c1ec093f78c503ed020637be','20932044-6f81-4b6e-9c0d-cf1ae1b402e9','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('79e72042fff3a6aa4ba5c32cdfb6f8fc02b1fc29','20e27931-2db3-4795-b08c-2ebb1cd49533','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('1b9ab001d57ca2a378a01ee70479c915a6ed2784','21458cf4-4cd3-4516-a4c9-2c8717217e94','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('7f8cd7b21eeae7881571215ebd6350bad26415b5','21458cf4-4cd3-4516-a4c9-2c8717217e94','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('6d304f451430c5040ec60551be07da7bf0cd5b1f','22f11d62-ccae-4de3-9fe9-982f03625a00','818d6f41-ad23-4925-8e81-4427b848f715'),('1c2e463cbb34d0cd94335ba82387fa28f1a0433b','239e425e-7d3c-4e45-b64c-87e6fbf560c4','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('2963cfd8587810352f7da13edb6b48d68ce94f47','25bd55d7-9e11-483e-9708-56ff0bf8df45','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('bb94bc4bbbf029c4ac2135d1886418ff79ed7074','25bd55d7-9e11-483e-9708-56ff0bf8df45','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('ab4359f93c14c10b269801592c2e124fb803562e','25c6224a-a48f-42a9-923a-6a663d9543c2','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('af29358283c6c9c3ed58cf6614a3e93e39c1f011','26838ece-b912-42d0-9b51-9fc697fab8f6','818d6f41-ad23-4925-8e81-4427b848f715'),('9710203deaed4c9f146b7b909ccbc5f83110e4ae','27b33bfc-7e24-4a29-8218-150d2c6a0e2a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('3abe116ffcb06eef2b12978f7b0825d2d041993b','2940794d-5516-4239-b4a0-eb31340a6143','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('a50d9ded07d5ee513624230b67f23cd714d3ce16','2a95d7d0-58f9-4b4c-8756-cf3d4a51d705','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('084a0d544fa40cd9c7cf4f94b1a549ca7ad45212','2adbe136-1244-4ed1-bf01-a2779771f506','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('7b3c163b061ee41afbe80ca3556a67f00188311a','2fb60ddb-7921-479f-899a-62f3ebc688e7','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('5fa5623b2163ed8840dfd301b001de72761ce2d4','3121c00c-aacd-4f62-9ddc-15e7aa2effd1','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('939af1d9864cf2546c93e42556a2a38ae8fe8a76','313ef3d6-b6e4-4c36-822e-c4111360f7c3','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('41866eae3b9d2817cbda4982c04ae22a9df4596d','3229a40e-e201-4fed-8279-8309ae368589','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('a8c49f734b01cea4acfd6c7fabaf386eb3f1410c','369c9505-2288-4b21-9ecd-0d74302a94d6','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('446da1ac501f68e9113f304a0cbb619c1ae94291','36c1fb41-50e8-453f-a4e7-44fd910e0a2e','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('8baafcb24c0ad93b6f8944cb6a75b0388603cc3c','37d381aa-c5ce-447d-9021-fc313e68d9db','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('34a76db89dedd4138bd6a65059e21dfb76cdb3ac','3a31ba06-2ec6-4f13-8306-b13e9f1c147d','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('e4c08d54b2e95c600398e13d85f452439e39b841','3ab48dc1-9f30-4f5e-9726-01b6579e56c6','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('6e2299a449e3cd32ed769866b8f40022b663c516','3b445f00-f524-4cd0-9d5e-8defe53963fd','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('73b34062e863527a2fc46088477e5b7366e7df65','3ce5f731-c744-49fe-bb3e-17850a89a2b7','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('90a98eeabce1988310e3725ce801499b932aa309','3d95195e-f2dd-4e0c-8244-9adb53559ea9','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('cbdc1afda15bb912b90bf6f424bdee3ea6009e2b','3eb24a19-3dbf-46af-82c4-d4939ca0a3f2','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('22b28fbe9261954983b6d266e1ef608cdedc68ba','40e0b595-ae42-498d-857f-823767b4caec','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('e56819762b05a27f02eaf716715463d689e67e4d','4197f0f7-db3a-4a93-834f-c495bbad1efb','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('07b918b47b0dbd08112d885f24176a4bf6752a75','4249a7cf-4679-404e-aa7a-c685630e9b28','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('450045f75ab46e455d3759fb36a6fbf956c89c29','4305559c-3d51-4879-a7af-41ae7987f238','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('beb4dfae076ea0dcaa7e9cdb0df75c5d56e307de','439254fc-949e-4191-93fa-ad291c33c038','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('7fade715eacd41c3fa2cb3b1bbe988a2999f892a','441fd572-2575-47fe-a14b-2234166b7a2e','818d6f41-ad23-4925-8e81-4427b848f715'),('c314c3381530ff53ba13d4092cc5c0b7f685dd94','44835a53-b57d-4798-a1fe-1afb16da69fc','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('9d882642c6a9f566e17030213c0952daa24cea66','4584b0d0-2cac-4d24-83d7-42a11feb6f12','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('ff5bd6049779778ed4efd813944a9082d4187c7a','475a698b-cd6d-4609-8d30-01e91be91156','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('64fd054d0102767c187a28a3449ecf862e057191','49dc2da6-f5a5-4175-b8ba-1519b6521d68','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('ce30dbfc8a66646dc07b0c05b78ff92ef4b8ef62','49dc2da6-f5a5-4175-b8ba-1519b6521d68','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('de76ec54eb0019cdda895861c4db4401c67b0af8','49e08d27-f17d-4dd7-bf3d-135c637fb91d','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('16d469282afc7c5fa7cea7699343df6cc74984c3','49e5008d-7290-41cb-87ad-0dca9b9efd19','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('d92f266bd5a5dcbf0b35aaae8f4ba157766e25c2','4a1a216f-343b-47d7-8463-b096b64461d6','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('795e14def30f752f567e5949fa65252855fef9b8','4a3ef43f-ac05-4ff1-b809-65de3856a2cf','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('e153b3734da41c0491d7b285c6d21a1f92736a5f','4a7ef470-f581-419b-a5a7-e33f98b9b427','818d6f41-ad23-4925-8e81-4427b848f715'),('b2c4eab9b76d7f1dfed2c1bf5814595d8deeee83','4af77ead-849d-41ec-a579-bad15a9df25b','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('1f6bb9cbf26efcef4d1f2ce6fdbb07ac50375ea4','4b7da2a8-0206-42d1-851c-2106db148d0e','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('928edc48f41c7032591660d71da52d1a554b3ddd','4b7da2a8-0206-42d1-851c-2106db148d0e','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('1c0105d3fe2ff8272024302c5dc877a187b114f6','4d1632bf-8b27-4c3d-b68e-4f61ab07248f','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('8b4e5c0c87e217e45056920d01bda52f7032f8e0','4db7c8a0-f68a-432e-b27d-422b69d18f98','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('75dedf41ecc626958577d889ba8e4290c1bdd1e9','4dbb1b4b-f1c3-4434-b013-0cc94d442ec6','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('5446e1e81e3d0c36c5ae273869395937404cf61b','4e341b09-9caa-4051-804b-49bd23e17662','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('a6f5bcf07b2b9a02cd163eeead36404825312780','4e9803ef-7835-41bd-bc8a-ccad88a522dc','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('67a8d2d86b2cc486393fbd4c098c74348959c9f1','4ea11c75-02e5-4272-9255-cbc63e244944','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('26f23de1964052fe42a95894ccac4af3270ed559','51da7956-deac-490f-b12d-6ae0cf54fd33','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('be5c27cf32ca4162cc93de2fbf4eb13492e911db','51da7956-deac-490f-b12d-6ae0cf54fd33','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('b62316fa040be7598303e0db1357649eea5fedbe','52eee0ac-0301-43ba-9a5f-de34f85588cb','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('b4865e0feda5470e1bad2a6eeb2bb5ab9b7af742','531f792c-f595-4ba3-9ba3-53100bd01d3a','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('a85464e52d016b18e8431f8deb0870d475fffcc1','53fe449d-3d59-4b95-a3f2-5d2e84ce23f6','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('952d16098f4219979756537d9457d967db3146e8','5573c038-5f2a-4094-9da5-f1d5bb7bd20f','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('b78ba04fd0375eb8e698c3ec6b23eb063a3f2c29','55f41850-78f2-42a1-997a-afc3ff688154','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('919e105b855bda4e0abf5f05b6bb55078b8df2bf','568bcd5a-f3ab-43e5-ba1f-1720d5592632','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('fa86c5583b7ac61ce161598c36e8be1d1844726c','5690c192-e6fc-49c7-8736-d27c3bb7c983','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('33ee6ced91808cdb91d9f5d9cd60a68c7ab59a5d','57d27748-641c-4076-87d8-9196e7c85cb6','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('9fe944d901e44b059ddf03e824a8c7ed8a8e52e8','59280385-f47d-4937-acf2-fc35aae96528','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('79195c45f761e11aedb1fdeb58b9577d924b7646','59280385-f47d-4937-acf2-fc35aae96528','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('18ec876099a8aa9e80b05e7c49dbffefb637bf40','5acad756-c3f6-45df-a8d3-87491a2d6c31','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('c45388ed9abbd9b6646107a0298aa37233908de2','5b198a2b-4395-4fc2-8ca6-84f33673ff7b','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('36a7b537e70d2a3ff994057c6aaa94989db41f9a','5d603b10-c959-41fa-9ba8-728e16674b3f','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('d2392520cfbfce86adee33282da979fd7ae10935','5e092392-c81c-4b12-9e6b-a51f48de748a','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('8fa6ed9701ad80c848b4fa2c50412529d2ef8a4a','5ea2bc87-d203-47eb-814c-0b610b3c2aea','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('c6d669877d21c62d4d8d2f95039da2bb8557014c','60462e62-8a4d-4ca9-8cf3-3d7b62834079','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('ec8845a6f7f7588dc2b1ddf405afd66e14cb8808','60462e62-8a4d-4ca9-8cf3-3d7b62834079','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('8a12f246a932e625efc1b31d1de79e7b7cc4dc48','6101d2a2-ed8d-458c-bcf2-453ffdca2e46','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('b2b22c419a8df3414f29966a855be982cd23b5dd','613736dd-1aad-4782-ac70-eb9ac5410ea5','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('094755d18efa5415a0951cc5e6a167a69a6b1e33','61fd98e2-23bf-45d4-bdd3-e942be1cfd9f','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('bca1b04b0ebfdc502ba606650c4413571faf4708','62164d1f-d296-41b8-9cec-88da7a888a56','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('93d09454046d884f1f966d01e29406c9e823d7f3','62164d1f-d296-41b8-9cec-88da7a888a56','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('e03e90b9cb98d0635a026473fc0abe2bd703236e','62888211-fa78-48e8-9076-c507377647ca','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('8ad5c4e61c438953eb5c19035b218ae3bf8c8c99','631501a8-a96f-4295-a9fd-5a323cdff270','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('ccca2de84222b22d57df2978f107876c22736507','643b46f0-5c08-4c0b-9771-bfb367316a31','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('f7a13b71c02013abf1df13f9709e4ca74f5e902d','66613118-f9ff-4b1c-aa3a-2cd4fad64e91','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('19a4bbceb6d4efbfdc598d2d809ef337e03dffce','6753d07d-6376-4f39-b2cb-a9cee3729005','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('caaa098257da5904cb3438b53e69c419d6862266','690aeace-f632-45d0-8a0d-5dc52ad08c16','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('da4bff8a91d8d0843c66d4b7847a787ae8b99f2e','6a675238-bedf-471e-a6b5-58e9a34cde0c','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('dd3d0c62226622a79c8f1b1ea3e9f589cc50e792','6a675238-bedf-471e-a6b5-58e9a34cde0c','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('0302ee430b8a54da81b36d794e60a6a292cb08bf','6b6e4968-8031-4638-933c-a927a7222141','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('13ee80bff905261a56f69d88d37da813e1cd54ce','6c57cbcc-3c65-458a-a87e-0ea8e82ec10f','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('52ccf23250d8d53a99b41b6b11cfe54dd6576ecd','6c57cbcc-3c65-458a-a87e-0ea8e82ec10f','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('4c5d17b6392f6b6b653a5dabe7ec0a09dd7fefa2','6e591846-3acc-47ed-b3b4-668e1af45bac','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('6a45262c2e8dd2d129088381345d7ef235afaa65','6e591846-3acc-47ed-b3b4-668e1af45bac','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('1adf2464259f53d561fa7c9018b85ab45d146392','6e90bfa9-8b3c-4d55-b695-1e903c83c28b','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('7091e590ed650a7b3cc871d2ad74665fac64e32f','6eb34fc6-ff6a-4311-a9d0-edcd55e3bb8d','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('15d16c44dede84bff26a60685ed08234f9d1b70e','71bb33cc-5a44-4e37-9121-0c1fd23f6ac5','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('9c2c474cdc5f40dd5fa7e28acb76d11b32df2564','71bdaf2b-a8a8-4aa9-825a-db27f7940bef','818d6f41-ad23-4925-8e81-4427b848f715'),('e322751ab89b46c3bd2d9a143b7c08a26746bf16','71c48578-41fa-4c4b-8daf-8e67fb0058cb','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('5cb295ae5e538f2e039b68ce9c60d991d1d1c1ef','748aff7b-b326-4028-914b-377a9fd841e5','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('5fe97e4c09269e66cb9310ec9718a9aa8610b804','74d00579-9e98-49f4-a0be-3df551646527','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('a482f8380ddae3360e3018dda76ec72538cc525e','74d00579-9e98-49f4-a0be-3df551646527','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('e188a8e8e62005a25dfb17233a5148f7c81fa0aa','757f5c93-2dbb-493a-ba00-bc76da0ec314','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('54e37f8bf00264f29bf905e1290787256fa0c3b0','76b37e64-850f-42e9-a7ed-f93465de6f57','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('00ca90fa9c958b23a4aadddf0000bf9d7c0280e6','76d31536-deb2-46af-aab5-8f33c16241de','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('5879bc3d2e6f8008070b63aa1d3cec9f4368c2be','76d31536-deb2-46af-aab5-8f33c16241de','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('87141ea53cc7cfe9ecd4674a585699a5549ff2f9','774fdab6-8543-455d-ad08-597eab1f1dca','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('cea892dfd039ab961e6ecb7c087a401f5f332809','77da3bc4-54c7-4a9d-95d6-f1dffe410e4b','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('90aae1ac1577bf9f6bdedaad3b33b47ce49f4798','7a492c90-7fa5-4438-9c81-281528ae81d7','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('f720fc373c81a0a0c1af11207f49ce1559e251dc','7b882bf8-1cd0-4845-8e0f-36251b251b22','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('f13250815e6a9e89cc90d94ff15d417385e57461','7c7b5ba4-a89d-4b62-89bd-b18c3a9a5ab3','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('e96795eb94b425906cae2a7f99301606eb87d6cf','7e8a77b4-d30f-4152-b020-0b468b73080e','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('a045c7792974836904c7e538cea5c6fe5ff77d26','7f375ba4-e6b2-4c14-a77b-0a8f4871261d','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('07bd49c1cf7ec25e6e7275b0d4bd909adb6f7460','7f5a1f91-3ac4-4892-9ab6-70f251be6803','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('fadbcecd53a513c1934abf5253053c86ce8df568','7f5a1f91-3ac4-4892-9ab6-70f251be6803','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('354fe45fb209de87adc861883adb48d8255203ed','80cc366f-2e40-458e-9910-25bd1f778151','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('43bcc0e20a33c65cef358a11430974808c7d3008','80f36a21-b95f-4761-a7f0-d8f2b62cce6c','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('d041444c0c34a87eb15275c8ca3743916dd24943','8124d41e-fbe8-4d5e-bcb4-bc108e7b1470','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('50180647dab475bde1a3f98e09fd9659261e2e02','81d3c7ab-f981-43b3-aecd-f5b425749cf3','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('0539ae8bc889f61f7807f0554b1bf755bf0627b9','82567d17-5dcf-49e0-a69b-deae63668593','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('f6417232a2c158c835b02fe01e9975d723a1652f','82947cb9-b30c-485e-b2ef-9e0271d71363','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('f8561ffbe1e85fa75918371c8b918cd7b4c308a3','83fae473-baf8-4cc1-9073-bbbd1ee00754','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('983d9b973a3a395ea6915968bf83062fb30d71f1','854a4a30-a2ae-4dfa-bb1f-4949f4d36cc8','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('65d20d01e65112592b0f9508e591f6583c78b6f2','85f9311c-cd32-417e-b356-526cfb2db35e','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('8d456910661085178c72ba0687113f3442c8444a','865b9783-65a6-4f5c-81d4-34774bcf1972','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('b8a34814c0d5a9e0dac7b755d77ed1b4e64b5434','86c16765-72ff-4995-b30f-6356eb962406','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('cf480342bae8f395ce496640826f90c377d57d99','86db6a26-d86b-4c36-9405-d60a1f22d673','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('e23102e22afd4fd77a3c7a9192fad821989192d7','8a10f753-69f2-4df7-a368-a6f8f86a8dc0','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('85c5813e9639a673d2fe1164b3499f8b91f820ba','8add7c2d-6ad9-4cd3-97e5-7548b6482c14','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('08d421a71535f5925470f8b866239576b87ac6dd','8add7c2d-6ad9-4cd3-97e5-7548b6482c14','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('c150f0751903ef3f6f28ed25c068b7d0f76aac60','8b1d0f63-43fc-4bfa-bfcc-0d222df0456b','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('1fbc512fd153ee236308617d7c7023d0606762c6','8ccc968e-f57e-4bcf-88ef-4ad120f6df3d','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('c6be843d643af2d9a7623ed5e770b7b6088c5666','8ccc968e-f57e-4bcf-88ef-4ad120f6df3d','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('7e20fef86618cf33f067184237fa6be91781eedc','8e703941-2a56-40fc-b896-6eb10eac3fa0','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('89896caed0de5e24daf88e882393d82089c02a93','8eca38eb-9a95-4382-9b01-8fdde916e699','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('631c7e28360e82b7b5c18f18d9df4212fe249051','8f7560c2-02e9-443f-a92f-90e7f5fc3ca8','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('21fd184e1a98d1901ee26ce652d07266c599734b','90290f5e-872f-4b12-b889-d4e1c329abe4','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('c4a580d7a1e6de7a9cd9e41d092c280151f82fc7','907d6cb4-ef5a-490c-8efc-5cf0a5347366','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('187a21ac65660332b73d8657735d372ffc3abab7','9098090c-851b-4896-ae6d-f103595b96a3','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('c6ef06f88c4083324dc58cf4c60a18e90c932ddf','927b8f61-5d4f-4c7a-a2ab-ffac481c80ce','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('d7fedae1e438096c49a63bc365d7deba99ea7250','93360cdb-a413-45d7-af6b-cdfc6838ecfc','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('c041e866f405a2335ea787520c5aad0f95f311d2','934818d2-5082-44e1-841d-c522b7ad45e6','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('0cf0bc2d61d1209bd7a6c478e8a5843acdfa30e6','93563d58-f4bb-4c49-8e9c-dc88c5d6dae2','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('adf644004e0daafccc32459bdee44ef22d5f6618','95894197-97f2-4026-bbb2-e74ae88e597a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('cc5b83434d9261477e5d6b6c9d907735ff4a793c','95e3d70f-a8e3-43a7-a8d6-f565cfee62bc','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('a5b503e8f2f985cdd825e13c03de13c538d91bdb','960d6be9-5ec5-4955-99d3-763ab551cd3d','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('2806493c3e789d6fa34ff00e775c7ff43babab14','9631fa40-0417-4e75-a23b-678bf1f6bee4','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('be1c14f23265075e0df4cabc290615556b759394','96963d4e-aff7-47aa-96bd-dfbc72551d25','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('89927e3dabf87aeeeb43a4730943dc21d75ea4e9','974bba1a-38c4-4a6c-94c0-2571abd7f342','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('09b7e712d3fb631de19572f4beb572eecc11d4ce','97a32e87-6c22-48d2-958e-fb0b83d3c660','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('f5de7099c31fb4ff2aefa8960c089acf3b02f2d8','98e1103e-24a2-4b63-b89c-6c3a3e303447','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('bc1bd90c16398febcda32c61189a5c635c6ebb40','9d2907cf-c90d-4096-a66e-559ca2c107cd','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('2c89bc06efb65106a25e029982d1d668ba59645e','9e670ce9-c6f0-485f-9040-8562ae36c4bd','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('f2f4485d9d1f86019d1bcc2faf2106301f2bfdf6','9ff5ab48-fbf0-4bbf-8a19-6745d9617df7','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('d82234194d58f2063aeb0bf27976e28b6f195f7b','a08aeb34-af8d-4ef3-8007-cad0e0f2a2d7','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('4bba792c8355949766bc6fd486026f86e98c7fe4','a0a9e71c-5468-425b-9a29-4fcb5c544b36','818d6f41-ad23-4925-8e81-4427b848f715'),('b30deb218875167a7caea6f578a306e4a4edcf3d','a0db7f21-e9da-4cd7-9887-8efa8a2579ae','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('0384d5bb36645f85a6d99abe9346d17acde76e95','a164299c-e525-4d0f-b8bf-015b01263f6d','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('38f97742b8e50215f47a05c338777dcd89afe7d3','a1f2c7e9-63af-4dcf-b1b0-c701d052f5ae','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('adc0c587dc46b4846831850a9bad94cb89581a71','a2ade0af-0efa-451a-a4c6-b7b51bbc9905','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('9377435ba54eb8408706a70d57dbb94f9daa216e','a2fdcdca-2b58-47b8-84d1-32983f1515a9','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('579b23f344f2506b8cca59e042ad2c33c8f19bed','a358df43-9b12-42ec-b1a3-fc07283b87b6','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('e6e421f954836f4637af61cfc2ef1299050d24ad','a4b333b9-c779-4ffd-bb2a-00144194e81e','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('a634d837fdd946e637b6e2a5b9546654f9618d2c','a681024d-4fab-4a8f-8644-de078e20be43','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('99d558b882bc5f85d8ed7b86484a8753b301fd20','a7e55f18-6bd9-4bdd-b4f2-4c6893eeb264','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('57466d33d0ce31e3dcf7d4f4d83eba836ed7220a','a879f064-44e3-4e43-950c-b963cab3c976','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('e8a62b7be79841040879d8b721e01930ac0e6d43','a8ec1f26-8bce-4b14-9f98-4c3998d9b101','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('37405b2abd5f4419cf9d549e564260efe3803bd1','a978894e-362b-45ea-9b81-feaf3aad243f','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('09bd4f214dfcdeee63a7aa96a388dc800e4c7a13','a9b40f51-547d-4160-8866-48b323d481cf','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('fd5415e8372037dc3cc6beb5b0e4254ddf0b6e98','aaa72f4c-d7aa-40cb-9ac5-5650e94f7de1','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('8378003b7978adf44c89433190a7db63e34a134e','ab48d0d2-8d0f-4c7f-bf5b-27aa2b7dc889','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('aa9b697bda534bbe55e09c47652414a66de750a5','abc7ffd4-0d89-49a2-bfa9-e2b0998c5a94','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('918cc0ad09224bdbb0c68f72544463403717a811','aca3056f-88c6-4299-b01f-fa937bb0fcca','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('75e3c1b086774e0411c6f73c0e9ab5da4bb61959','aca3056f-88c6-4299-b01f-fa937bb0fcca','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('d1762d389df4b9d260104c4f46b31e07d36369a6','ad538c93-d5a2-45f6-89f0-c9eb1db1ccb5','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('72fa55f160b6b898bafccea32c71223fdec54ebd','af2bee81-b17d-4cb4-b7d4-0c3fb4995b9d','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('b7275d9233ac6045f6a0e635a41f584307740e71','b0447eec-6a38-4e2d-9f5e-d3be2c5eb885','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('9a26dd18d7f33511b4a98d838cae09543ce5f171','b06b98b0-c036-11e1-b910-cd9212b8c0d3','5e5717a0-c038-11e1-8b8e-234ffa2d0a60'),('3f18e71b45ef07f3110e3cb418bdeb1be0c9ef83','b06b98b0-c036-11e1-b910-cd9212b8c0d3','b416cbc0-c036-11e1-b5a6-216cccc3b1fc'),('0d1b55b8fcc43f60cc14f23a191af8f55e805609','b1da919f-c2b8-490a-baf8-c9280ad6be2d','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('175ef5d6627ce46aa2946854260483fb58b15ee0','b4f9c0da-6a62-4c2b-b45f-edfa3112e55a','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('ceb7558166f232d00c3bece30fce74e1796dc735','b5507db2-2744-490f-ad34-887de0e83f43','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('7cfb040049d52a3e0159f899cc69084e96ce10ed','b5a2ec4e-2cee-475c-a1e0-9fb80eadf91c','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('bddf8eede869847dcdde7dcc39ea4333404243ad','b5c0cd23-80e8-441e-803f-48b856c377c3','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('a050994ae58b519a21cde1cff989e57aa0e3335e','b5d7d0cf-db75-463e-ace2-dcc6701d90fb','818d6f41-ad23-4925-8e81-4427b848f715'),('0fe90c62c0509fc4b064ddd9a4898ea5117ec29d','b60cec87-f677-4c1c-aa13-bb1c6973c087','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('2fe64c65dd03a06d8f11cee356a3965d9fec5e42','b6287ef8-30b0-4387-a251-dc58473713e2','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('a43cc4d37679abd9e8948ace57e64340a7523277','b6731a11-aec5-4bdb-aafa-803ca70a4ed5','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('fa56f566f18d5b31170ed765f57f1a266b1ea926','b79e2a42-0c24-4762-8240-8c3bb68d3f9d','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('5bc230e4397c864d6df1564fe5209a748bd97b51','b93c6fac-93c6-4593-8d06-32249a8c8c0c','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('3157f625bd82513e54b64b7614467b9a4d015be7','bdfba64b-fbb1-48fe-bbdb-35c007174f06','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('ca2417eb781a07d2d48f63e7cc8fefbf256befce','c2323400-76c4-4b06-a827-6f100e27417c','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('b9eabb1f73601c6569f3c5e1fc28237f9a89edec','c2887ac3-d9ae-47f6-92ff-4c52a3131319','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('4ce5f92c4161295e6d76d97e0a0395f43c78ea3b','c31b30c8-36f1-4fc9-bff9-8871eca7e97e','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('1ebcb948db5bd9bf064e704615a4cf8fe0f3b66f','c37d75b7-8d3e-46ac-923e-1a46cfcdff88','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('34be1efae3dbe0dd6953efd2a6a3b88bd8eacd60','c38e7522-0861-42e1-b5e3-21588050f74e','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('003dcb3e556240d9c59edbb101ffc1dd19a8eb0c','c3e7e00f-3eb3-4f1c-a60c-662fa4ec9f68','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('1d8c1aeedb3a0bf57ca98325d543e32e349828b5','c6ed375e-fdd7-4238-a414-cfaaaebfba4f','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('e1f261fab9f15d5a65e506e410825006b492b5bd','c6ed375e-fdd7-4238-a414-cfaaaebfba4f','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('974ede8379fc855b59e9b60b245999518c2f568a','c7412886-e6a0-47d9-8f22-68155ba16bfa','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('f5c0f3a69eec23af66886e718320ab453f6d9250','c7d20749-4edc-4274-91e0-f7d8964b8645','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('736a2062d2e9fd3e43571339e580f0d6b36ee6bf','ca6e0ccd-93ab-484b-b10d-45a7ddded655','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('46730a5c16a129f75bb8bc8b8c23c4aabbae92ee','cab2e612-4d23-4846-b96f-fadc464a8829','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('1b7115034332f2f6608ed34b3867a3cb340ef6a8','cbef9251-9c7c-467a-91dd-9b7767fdb424','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('a7d41ca5bfe188a42417e114156ec9efe929c1d6','ccc02b8f-ee8c-44df-971b-a1ce8a3df1bc','7851a510-7e0a-4cc7-9ce3-2587aa502163'),('432a6ba29c3b4e0c8439673a71c519f9fa17aaaa','cd3384d4-c93a-4584-a265-0cb8036b519b','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('96c71a6e72edbcd7418651f8d47455e5a71af525','cd4f0099-204e-4b46-8382-ca6b425de6ae','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('46dd1e9e9986d8501476f3efb78229fb802f89ee','ce328adc-6857-4c76-b8fc-4dae85a1df4a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('9af58d0a9c0e1120c0f5a7560fc8519266c622e3','cfbfba64-6b4f-4dfd-bafa-1dc4be9b1a7a','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('cc5294e85e48cb243dfd4e422c39a2d8a7dce052','cfbfba64-6b4f-4dfd-bafa-1dc4be9b1a7a','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('40e507dd3fb360bb04a95aae843192990b0f4ec0','d05c8843-3afd-470b-85be-95f66c2f0ef4','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('f2ed1454b00d21a047409ba6a7cb26512d5cd211','d2143584-77db-487e-b528-331771538f7a','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('63117d1b9081379326b6dcbe4c397b3129af7f13','d248f536-08f3-4f8c-bc82-41f8ebb3d25d','818d6f41-ad23-4925-8e81-4427b848f715'),('a279c30278495f13c89d9fc3bbeafe9ab691aca9','d58c4230-e784-41e9-ae0a-7987c1fde8a1','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('035b6a02c20445acf7a93bb7d397d08dcc30cffc','d58c4230-e784-41e9-ae0a-7987c1fde8a1','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('c21b9cc5d857d407e6d75fb8749580ad54c24181','d60765f7-3509-49ed-9f7a-0cfde5f02764','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('1f180b92ef50e37573cebf83de46a4f5757f2537','d6780972-6921-4cc6-8309-87614caab6e4','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('4a5b37925b717abb7c4f34ed1743cb893e79e5df','d6bf7bd4-65a5-4697-ae53-4f8d72d5d6bb','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('ceb155ed9862942d36fd384a8bcaf9f11a79b68c','d78051e8-d72b-41a9-be99-8d5b8913a3d7','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('9a2d563a661a3bbbadf2c89c93f6ad0f03237e17','d8609248-2034-4aa5-a331-733f406ec55a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('5a989934df527765232459c973920ab9a2f63ddf','d8b158df-3efb-4e6a-9499-2b6585b9cb21','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('99d9fdd28ac1d46ca746678d7c50c54adf3ef91a','d8ddd2d6-df6f-4d14-9020-df4bb022bfd7','cf9b9fda-76ce-46c0-bfba-3e930510ac21'),('ae39a92197230ea2ba98fc06411a48b8634e8344','d8e2fa89-50de-4a1d-84c4-cfa7bd8a01af','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('4daac8c49223442e31a88c0837b3218b97230047','d8e2fa89-50de-4a1d-84c4-cfa7bd8a01af','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('a77495c6d98fc41d09be9158e9f50b3e161df6c6','d954b588-aa51-4b34-b127-7f2598ca8165','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('0497c22a01692b3d124211d92e2b4add84887ab4','db44adb9-b4a7-4bbb-8e38-a2a551637a25','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('04c81401fcd0aa24c830f6a8743ba9b8b7137a5b','db6c0cec-012a-41ca-9f78-f524222fdcb5','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('61a90a78556cc40ef412c5d00988cdc945179e8b','dc744462-dee2-4e27-8ec0-b58eb0d5626c','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('81216bd155e951b094b07a59827ad6366b52d1da','dda95d3e-68a7-4a30-97d4-449c1058b69d','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('bfe012f30322307b4fa5c0a0cf24a53c2ab13f7d','dda95d3e-68a7-4a30-97d4-449c1058b69d','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('b264626ee975ccf02d345f3d57acf1f7d6d6b10c','de9a5576-3d14-4f35-807f-6f021c43b891','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('b11ecdace6871f97be9f24b94f6700d72f53fda2','df7eaa0e-d638-4dd4-b7af-785b54238ce1','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('7defb73ead4a428ec1e7f3ce6d60f2a239a669a6','dfcb5374-32a9-4520-b130-dfe32eb53052','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('95894fb447df046eb50b7f136d694568c18ef3ae','e075611c-396c-45c6-9573-4635968b3742','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('dc2a56f1ca3328c10608b5a47169f3e43650d20e','e15e6857-f83e-4e4c-a55b-5c2df5de9384','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('9f0489f8537f186dcebc6f9668440bf193069e79','e18dd566-3861-49b2-bf9b-ad73f2c30645','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('b3d13fbe413dc2a2b5c0ccd337e2a27287ed947b','e1cf87cf-8db8-4e2e-9386-d522d1e9af8c','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('cd1d7cafd3ec3ba1bc71e38500383a1a24e13538','e1cf87cf-8db8-4e2e-9386-d522d1e9af8c','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('3af1641336c9befaf0ea8111765c8b9a8d75a5eb','e226f587-ae4d-4b5b-8145-35bab2029de4','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('7dd34660d799fdb458b49e48086ba92244dcd7c3','e28c8f52-2b04-40b9-ad76-971e60c3b76e','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('59f479584ada13ffa1d373a07559ad4a0bb1c0b3','e4412315-d6b4-4538-8269-e135b17e71d6','44a9f682-5cd8-45b8-a139-73542e7eaaa6'),('aaf07d845a31ea9d82f6fc049b3fd1a61b1eaddc','e6302125-90a2-477e-b86c-a9d022a11a7e','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('04d71f052fd74963a2dd02167959c7ec07766d7e','e6302125-90a2-477e-b86c-a9d022a11a7e','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('f79101dc61dd148d42e210a7eff431fc46fd6dab','e6a2c265-dc98-4291-90d5-15a34d4a12c9','a7e5b8a6-29e9-43ea-9823-c98702239cb8'),('2bf752b0a152dbab12f2b774cd204dd25ddefaf8','e9296a4b-c286-4b4f-aeed-4f6d617a75ec','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('1e015b7e590459b3870a55cc99c4d61eda1c8661','e943d075-a6b0-42c4-ad91-f4954d0ae190','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('843c5a5caad1550aa0db9e4b378fc47ff5d4004e','ea0055e9-012e-42fc-8318-0018c9a69e0e','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('c49a4eb82e51ea239a7971e08d5928cf88e797cb','ea0055e9-012e-42fc-8318-0018c9a69e0e','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('bfa514e0c402a76cbafcc64122237866029fa85b','eaaab8b0-6996-4b38-9f56-d5b5128146a6','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('2a48fd410a083f90aed4662af042623a9f63d0a2','eb2f8c86-2c96-48ff-878c-e651d58f5321','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c'),('1e151695bab0f56ee77210dbdaa8823fd6e89009','ed6a84bb-061b-45ca-948d-0653b7bf7d79','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('67545cba4d73923b3bc744731a82880f8bbb1295','ed9db80a-b640-4afb-9534-d8f10d505f01','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('148c0a53a138033558a3268439f75ed130d1dde3','ede4cf35-f523-441d-94d4-b700ac18bab7','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('ac93a047d21c6a186676607a431af7ab973c75b3','ede4d5b5-b282-4484-a186-37a5fd8e3436','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('41e771cebb5f9022054758718efbdaee3d333232','efe14e17-dd34-4d08-9872-afa6a1e6fc78','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('26cee646de1f3c29beb7a634efd878ab89cedf03','efe14e17-dd34-4d08-9872-afa6a1e6fc78','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('2afa608d5fdeaf21ded0ce6ae1aa04f1089ad73c','f1dc0b5d-137e-4bab-af88-01be86b6fa31','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('2ea9c642407c4141b14135fdd157c32e556290bc','f3b98d07-af65-4989-9c89-4ce2d658735b','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('aed742de598dd85caa048bc6d4dc9fb2a701635d','f5364c8b-f1f1-437f-8a52-db90b05c128e','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('88cc0c59a370bb536f5eebea3c88754ac05c7a3b','f5364c8b-f1f1-437f-8a52-db90b05c128e','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('5ce698dbbae2be26c1b4f7aac73ff506338ede93','f5d25705-3e00-4f57-8468-d4a1314a320b','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('ab9ba09e7ffde0692a63eb2f55bbee278e7c749d','f6667482-8395-4381-bda2-6dbab1bdb697','4e9c8415-2fe2-4d6f-a723-f02e47e948cc'),('0068bd328d37d11e5c982684b43d79589443bbac','f6667482-8395-4381-bda2-6dbab1bdb697','ae9d44ba-8741-47f6-9f6e-92b8c385ebee'),('3be2d6d00fdf1222d017e279c2770e3e128fdfd6','f6a3d05b-549e-4a01-9c78-14fbdbfa2fc5','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('a0688f4a44e42e329390d4dd494cdf2f235a1346','f8085d71-ca7d-4892-82f3-8263b341a0f2','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('c2e37661700573c6adb56cb699e72fddc4c20189','f8bb22a6-dc97-4d29-af71-b7e2893ae20b','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('1bd9a975e057c89294608fad882ff8d26832ffe9','f9ef01af-816a-4b14-b40a-ab23d0ea867a','cb6a4c0e-51c4-4cb2-b699-7e980bee116b'),('c940c9ef21d05782d29ad35986529d036045020d','fc036175-f307-4552-b281-41ed5be9e5d3','7b1822cb-492e-431c-9bc1-1bb529af5cec'),('8d3a20e0d19281a3d26f3db472e9ae1daa79ce91','fd07b700-4b24-408e-958c-2c34a37a3a87','2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0'),('b3919991d3022bd392cc8c5f7c2189f6be15321e','fdbcce62-ee0a-40ed-846d-49390efbe78c','871825ef-b27e-498a-b8a8-7a043fb56e1a'),('1444d2cfcc6367207be715c4fc4a0f898f06794a','fdf82ace-1c4e-4ba2-9996-c0d0daa01ba9','9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c');
/*!40000 ALTER TABLE `saml_persistent_id` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_provider_uuid`
--

DROP TABLE IF EXISTS `service_provider_uuid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_provider_uuid` (
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `service_provider_entity_id` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`uuid`),
  KEY `service_provider_entity_id` (`service_provider_entity_id`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Lookup table for UUIDs for Service Providers, provides a lev';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_provider_uuid`
--

LOCK TABLES `service_provider_uuid` WRITE;
/*!40000 ALTER TABLE `service_provider_uuid` DISABLE KEYS */;
INSERT INTO `service_provider_uuid` VALUES ('2db1aeae-9bdc-42bf-8fd2-bba0636fbeb0','https://engine.vm.openconext.org/functional-testing/SP-AA/metadata'),('44a9f682-5cd8-45b8-a139-73542e7eaaa6','https://engine.vm.openconext.org/functional-testing/Wrong%20Value%20ARP/metadata'),('4e9c8415-2fe2-4d6f-a723-f02e47e948cc','https://engine.vm.openconext.org/functional-testing/Loa%20SP/metadata'),('5e5717a0-c038-11e1-8b8e-234ffa2d0a60','https://serviceregistry.vm.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp'),('7851a510-7e0a-4cc7-9ce3-2587aa502163','https://engine.vm.openconext.org/functional-testing/Two%20value%20ARP/metadata'),('7b1822cb-492e-431c-9bc1-1bb529af5cec','https://engine.vm.openconext.org/functional-testing/No%20ARP/metadata'),('818d6f41-ad23-4925-8e81-4427b848f715','https://engine.vm.openconext.org/functional-testing/Right%20Value%20ARP/metadata'),('871825ef-b27e-498a-b8a8-7a043fb56e1a','https://engine.vm.openconext.org/functional-testing/Empty%20ARP/metadata'),('9ca10059-c8a2-4c21-9479-b8e0bf1e6c0c','https://engine.vm.openconext.org/functional-testing/Dummy%20SP/metadata'),('a7e5b8a6-29e9-43ea-9823-c98702239cb8','https://engine.vm.openconext.org/functional-testing/Dummy-SP/metadata'),('ae9d44ba-8741-47f6-9f6e-92b8c385ebee','https://engine.vm.openconext.org/functional-testing/Step%20Up/metadata'),('b416cbc0-c036-11e1-b5a6-216cccc3b1fc','https://manage.vm.openconext.org/simplesaml/module.php/saml/sp/metadata.php/default-sp'),('cb6a4c0e-51c4-4cb2-b699-7e980bee116b','https://engine.vm.openconext.org/functional-testing/SP-with-Attribute-Manipulations/metadata'),('cf9b9fda-76ce-46c0-bfba-3e930510ac21','https://engine.vm.openconext.org/functional-testing/Wildcard%20ARP/metadata');
/*!40000 ALTER TABLE `service_provider_uuid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sso_provider_roles`
--

DROP TABLE IF EXISTS `sso_provider_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sso_provider_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:object)',
  `organization_nl_name` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `organization_en_name` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `keywords_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publish_in_edugain` tinyint(1) NOT NULL,
  `certificates` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `workflow_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_persons` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `name_id_format` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_id_formats` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `single_logout_service` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `publish_in_edu_gain_date` date DEFAULT NULL,
  `disable_scoping` tinyint(1) NOT NULL,
  `additional_logging` tinyint(1) NOT NULL,
  `requests_must_be_signed` tinyint(1) NOT NULL,
  `response_processing_service_binding` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manipulation` longtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute_release_policy` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `assertion_consumer_services` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `is_transparent_issuer` tinyint(1) DEFAULT NULL,
  `is_trusted_proxy` tinyint(1) DEFAULT NULL,
  `display_unconnected_idps_wayf` tinyint(1) DEFAULT NULL,
  `is_consent_required` tinyint(1) DEFAULT NULL,
  `terms_of_service_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `skip_denormalization` tinyint(1) DEFAULT NULL,
  `allowed_idp_entity_ids` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `requested_attributes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `enabled_in_wayf` tinyint(1) DEFAULT NULL,
  `single_sign_on_services` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `guest_qualifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schac_home_organization` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sps_entity_ids_without_consent` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `hidden` tinyint(1) DEFAULT NULL,
  `shib_md_scopes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `policy_enforcement_decision_required` tinyint(1) DEFAULT NULL,
  `support_url_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_url_nl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute_aggregation_required` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sso_provider_roles_entity_id_type` (`type`,`entity_id`),
  KEY `idx_sso_provider_roles_type` (`type`),
  KEY `idx_sso_provider_roles_entity_id` (`entity_id`),
  KEY `idx_sso_provider_roles_publish_in_edugain` (`publish_in_edugain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sso_provider_roles`
--

LOCK TABLES `sso_provider_roles` WRITE;
/*!40000 ALTER TABLE `sso_provider_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `sso_provider_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sso_provider_roles_eb5`
--

DROP TABLE IF EXISTS `sso_provider_roles_eb5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sso_provider_roles_eb5` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:object)',
  `organization_nl_name` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `organization_en_name` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `keywords_nl` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publish_in_edugain` tinyint(1) NOT NULL,
  `certificates` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `workflow_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact_persons` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `name_id_format` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_id_formats` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `single_logout_service` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:object)',
  `publish_in_edu_gain_date` date DEFAULT NULL,
  `disable_scoping` tinyint(1) NOT NULL,
  `additional_logging` tinyint(1) NOT NULL,
  `requests_must_be_signed` tinyint(1) NOT NULL,
  `response_processing_service_binding` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manipulation` longtext COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute_release_policy` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `assertion_consumer_services` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `is_transparent_issuer` tinyint(1) DEFAULT NULL,
  `is_trusted_proxy` tinyint(1) DEFAULT NULL,
  `display_unconnected_idps_wayf` tinyint(1) DEFAULT NULL,
  `is_consent_required` tinyint(1) DEFAULT NULL,
  `terms_of_service_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `skip_denormalization` tinyint(1) DEFAULT NULL,
  `allowed_idp_entity_ids` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `requested_attributes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `enabled_in_wayf` tinyint(1) DEFAULT NULL,
  `single_sign_on_services` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `guest_qualifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schac_home_organization` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sps_entity_ids_without_consent` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `hidden` tinyint(1) DEFAULT NULL,
  `shib_md_scopes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:array)',
  `policy_enforcement_decision_required` tinyint(1) DEFAULT NULL,
  `support_url_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_url_nl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute_aggregation_required` tinyint(1) DEFAULT NULL,
  `signature_method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sso_provider_roles_entity_id_type` (`type`,`entity_id`),
  KEY `idx_sso_provider_roles_type` (`type`),
  KEY `idx_sso_provider_roles_entity_id` (`entity_id`),
  KEY `idx_sso_provider_roles_publish_in_edugain` (`publish_in_edugain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sso_provider_roles_eb5`
--

LOCK TABLES `sso_provider_roles_eb5` WRITE;
/*!40000 ALTER TABLE `sso_provider_roles_eb5` DISABLE KEYS */;
/*!40000 ALTER TABLE `sso_provider_roles_eb5` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `collab_person_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_collab_person_id)',
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:engineblock_collab_person_uuid)',
  PRIMARY KEY (`collab_person_id`),
  KEY `idx_user_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('urn:collab:person:example.com:student1','021ec6c5-fdc0-4115-9c33-db242580186d');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-02-08 13:36:05
