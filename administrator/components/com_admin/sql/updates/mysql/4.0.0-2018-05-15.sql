--
-- Table structure for table `#__workflows`
--

CREATE TABLE IF NOT EXISTS `#__workflows` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `extension` varchar(50) NOT NULL,
  `default` tinyint(1) NOT NULL  DEFAULT 0,
  `core` tinyint(1) NOT NULL  DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL,
  `created_by` int(10) NOT NULL DEFAULT 0,
  `modified` datetime NOT NULL,
  `modified_by` int(10) NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_extension` (`extension`),
  KEY `idx_default` (`default`),
  KEY `idx_created` (`created`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_modified` (`modified`),
  KEY `idx_modified_by` (`modified_by`),
  KEY `idx_checked_out` (`checked_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflows`
--

INSERT INTO `#__workflows` (`id`, `asset_id`, `published`, `title`, `description`, `extension`, `default`, `core`,`ordering`, `created`, `created_by`, `modified`, `modified_by`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 'COM_WORKFLOW_DEFAULT_WORKFLOW', '', 'com_content', 1, 1, 1, CURRENT_TIMESTAMP(), 0, CURRENT_TIMESTAMP(), 0, NULL, 0);

--
-- Table structure for table `#__workflow_associations`
--

CREATE TABLE IF NOT EXISTS `#__workflow_associations` (
  `item_id` int(10) NOT NULL DEFAULT 0 COMMENT 'Extension table id value',
  `stage_id` int(10) NOT NULL COMMENT 'Foreign Key to #__workflow_stages.id',
  `extension` varchar(50) NOT NULL,
  PRIMARY KEY (`item_id`, `extension`),
  KEY `idx_item_stage_extension` (`item_id`, `stage_id`, `extension`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_stage_id` (`stage_id`),
  KEY `idx_extension` (`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for table `#__workflow_stages`
--

CREATE TABLE IF NOT EXISTS `#__workflow_stages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `workflow_id` int(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `condition` int(10) DEFAULT 0,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  `checked_out_time` datetime,
  `checked_out` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_workflow_id` (`workflow_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_default` (`default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_stages`
--

INSERT INTO `#__workflow_stages` (`id`, `asset_id`, `ordering`, `workflow_id`, `published`, `title`, `description`, `condition`, `default`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 1, 1, 'JUNPUBLISHED', '', 0, 1, NULL, 0),
(2, 0, 2, 1, 1, 'JPUBLISHED', '', 1, 0, NULL, 0),
(3, 0, 3, 1, 1, 'JTRASHED', '', -2, 0, NULL, 0),
(4, 0, 4, 1, 1, 'JARCHIVED', '', 2, 0, NULL, 0);

--
-- Table structure for table `#__workflow_transitions`
--

CREATE TABLE IF NOT EXISTS `#__workflow_transitions` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) DEFAULT 0,
  `ordering` int(11) NOT NULL DEFAULT 0,
  `workflow_id` int(10) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `from_stage_id` int(10) NOT NULL,
  `to_stage_id` int(10) NOT NULL,
  `checked_out_time` datetime,
  `checked_out` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`(191)),
  KEY `idx_asset_id` (`asset_id`),
  KEY `idx_checked_out` (`checked_out`),
  KEY `idx_from_stage_id` (`from_stage_id`),
  KEY `idx_to_stage_id` (`to_stage_id`),
  KEY `idx_workflow_id` (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `#__workflow_transitions`
--

INSERT INTO `#__workflow_transitions` (`id`, `asset_id`, `published`, `ordering`, `workflow_id`, `title`, `description`, `from_stage_id`, `to_stage_id`, `checked_out_time`, `checked_out`) VALUES
(1, 0, 1, 1, 1, 'Unpublish', '', -1, 1, NULL, 0),
(2, 0, 1, 2, 1, 'Publish', '', -1, 2, NULL, 0),
(3, 0, 1, 3, 1, 'Trash', '', -1, 3, NULL, 0),
(4, 0, 1, 4, 1, 'Archive', '', -1, 4, NULL, 0);

--
-- Creating extension entry
--

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 0, '', '{}', 0, '0000-00-00 00:00:00', 0, 0);

--
-- Creating Associations for existing content
--
INSERT INTO `#__workflow_associations` (`item_id`, `stage_id`, `extension`)
SELECT `id`, CASE WHEN `state` = -2 THEN 3 WHEN `state` = 0 THEN 1 WHEN `state` = 2 THEN 4 ELSE 2 END, 'com_content' FROM `#__content`;
