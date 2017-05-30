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
# Table structure for table 'tx_themes_buttoncontent'
#
CREATE TABLE tx_themes_buttoncontent (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	fe_group varchar(100) DEFAULT '0' NOT NULL,
	linktext varchar(1024) DEFAULT '' NOT NULL,
	linktarget varchar(1024) DEFAULT '' NOT NULL,
	linktitle varchar(1024) DEFAULT '' NOT NULL,
	icon varchar(100) DEFAULT '0' NOT NULL,
	tt_content int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid,deleted,hidden,sorting),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_themes_responsive VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_behaviour VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_variants VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_enforceequalcolumnheight VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_columnsettings VARCHAR(1024) DEFAULT '' NOT NULL,
	tx_themes_buttoncontent int(11) DEFAULT '0' NOT NULL,
	tx_themes_icon varchar(32) DEFAULT '' NOT NULL
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_themes_icon varchar(32) DEFAULT '' NOT NULL
);

