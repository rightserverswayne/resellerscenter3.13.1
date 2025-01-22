--
-- `ResellersCenter_Documentations`
--
CREATE TABLE IF NOT EXISTS `ResellersCenter_Documentations` (
    `id`            int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name`          TEXT,
    `content`       TEXT,
    `pdfpath`       TEXT,
    `created_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at`    timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=#charset# DEFAULT COLLATE #collation#;