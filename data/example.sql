DROP TABLE IF EXISTS `rr_classifier`;
CREATE TABLE `rr_classifier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_system_classifier` tinyint(3) unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `rr_classifier_value`;
CREATE TABLE `rr_classifier_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classifier_id` int(10) unsigned NOT NULL,
  `code` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_no` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classifier_id` (`classifier_id`),
  CONSTRAINT `FK_rr_classifier_value_rr_classifier` FOREIGN KEY (`classifier_id`) REFERENCES `rr_classifier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `rr_classifier_value_l10n`;
CREATE TABLE `rr_classifier_value_l10n` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classifier_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classifier_value_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_code` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;