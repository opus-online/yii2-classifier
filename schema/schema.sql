CREATE TABLE `{{prefix}}classifier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_system_classifier` tinyint(3) unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `{{prefix}}classifier_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classifier_id` int(10) unsigned NOT NULL,
  `code` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_no` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classifier_id` (`classifier_id`),
  CONSTRAINT `FK_{{prefix}}classifier_value_{{prefix}}classifier` FOREIGN KEY (`classifier_id`) REFERENCES `{{prefix}}classifier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `{{prefix}}classifier_value_i18n` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classifier_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classifier_value_code` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_code` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
