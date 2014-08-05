# Pi Engine schema
# http://pialog.org
# Author: Lugal <luguoning@ferrch.com>
# --------------------------------------------------------

# ------------------------------------------------------
# Feedback
# >>>>

# Feedback
CREATE TABLE `{post}` (
  `id`              int(10)         unsigned    NOT NULL    auto_increment,
  `uid`             int(10)         unsigned    NOT NULL default '0',
  `username`		varchar(255)	NOT NULL	default '',
  `email`			varchar(255)	NOT NULL	default '',
  `content`         text,
  -- Content markup: text, html, markdown
  `time`            int(10)         unsigned    NOT NULL default '0',
  `ip`              varchar(15)     NOT NULL    default '',

  PRIMARY KEY  (`id`),
  KEY  `uid` (`username`)
);
