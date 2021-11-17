CREATE TABLE IF NOT EXISTS `mc_addon_product` (
    `id_adp` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `price_adp` decimal(12,2) NULL,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_adp`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mc_addon_product_content` (
    `id_content` int(7) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_adp` int(7) UNSIGNED NOT NULL,
    `id_lang` smallint(3) UNSIGNED NOT NULL,
    `name_adp` varchar(40) NULL,
    `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_content`),
    KEY `id_adp` (`id_adp`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mc_cartpay_addon_product` (
    `id_cart_adp` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_adp` int(7) UNSIGNED NOT NULL,
    `id_items` int(7) UNSIGNED NOT NULL,
    `content_adp` text,
    `infos_adp` text,
    `date_register` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cart_adp`),
    KEY `id_adp` (`id_adp`),
    KEY `id_items` (`id_items`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `mc_admin_access` (`id_role`, `id_module`, `view`, `append`, `edit`, `del`, `action`)
SELECT 1, m.id_module, 1, 1, 1, 1, 1 FROM mc_module as m WHERE name = 'addonproduct';