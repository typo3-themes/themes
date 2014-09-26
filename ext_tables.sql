CREATE TABLE sys_template (
	tx_themes_skin tinytext NOT NULL
);

#
# TABLE STRUCTURE FOR TABLE 'cf_themes_cache'
#
CREATE TABLE cf_themes_cache (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	crdate INT(11) unsigned DEFAULT '0' NOT NULL,
	content mediumblob,
	expires INT(11) UNSIGNED DEFAULT '0' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier)
) ENGINE=InnoDB;

#
# TABLE STRUCTURE FOR TABLE 'cf_themes_cache_tags'
#
CREATE TABLE cf_themes_cache_tags (
	id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	identifier VARCHAR(250) DEFAULT '' NOT NULL,
	tag VARCHAR(250) DEFAULT '' NOT NULL,
	PRIMARY KEY (id),
	KEY cache_id (identifier),
	KEY cache_tag (tag)
) ENGINE=InnoDB;


#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_themes_responsive VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_behavior VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_variants VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_enforceequalcolumnheight VARCHAR(1024) DEFAULT '' NOT NULL
	tx_themes_columnsettings VARCHAR(1024) DEFAULT '' NOT NULL

);