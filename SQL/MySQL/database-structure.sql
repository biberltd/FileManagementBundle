/**
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        22.12.2015
 */

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for file
-- ----------------------------
DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `name` varchar(165) COLLATE utf8_turkish_ci NOT NULL COMMENT 'File name excluding the extension.',
  `url_key` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'URL key of file. File name with special chars removed.',
  `source_original` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'Path (usually only file name with extension) of original file or embed source.',
  `source_preview` varchar(45) COLLATE utf8_turkish_ci DEFAULT NULL COMMENT 'If preview available, the path of preview file or embed source.',
  `type` char(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'i' COMMENT 'i:image,a:audio,v:video,f:flash,d:document,p:package,s:software',
  `width` int(10) unsigned DEFAULT NULL COMMENT 'Width dimension size in pixel.',
  `height` int(10) unsigned DEFAULT NULL COMMENT 'Height dimension size in pixel.',
  `size` decimal(5,2) unsigned DEFAULT NULL COMMENT 'File size in KB.',
  `folder` int(10) unsigned NOT NULL COMMENT 'Folder where file is located.',
  `mime_type` varchar(45) COLLATE utf8_turkish_ci NOT NULL,
  `extension` varchar(6) COLLATE utf8_turkish_ci NOT NULL COMMENT 'File extension such as jpg txt png php etc..',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Site that file belongs to.',
  `date_added` datetime NOT NULL COMMENT 'Date when the entry is first added.',
  `date_updated` datetime NOT NULL COMMENT 'Date when the entry is alst updated',
  `date_removed` datetime DEFAULT NULL COMMENT 'Date when the entry is marked as removed.',
  `tags` text DEFAULT NULL COMMENT 'Comma seperated and user defined tags of file.',
  `exif` text DEFAULT NULL COMMENT 'Exif data of file if it exists.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUFileId` (`id`) USING BTREE,
  KEY `idxNFileMimeType` (`mime_type`) USING BTREE,
  KEY `idxNFileDimension` (`width`,`height`) USING BTREE,
  KEY `idxNFileExtension` (`extension`) USING BTREE,
  KEY `idxFFolderOfFile` (`folder`) USING BTREE,
  KEY `idxFSiteOfFile` (`site`) USING BTREE,
  KEY `idxNFileDateAdded` (`date_added`),
  KEY `idxNFileDateUpdated` (`date_updated`),
  KEY `idxNFileDateRemoved` (`date_removed`),
  CONSTRAINT `idxFSiteOfFile` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idxFFolderOfFile` FOREIGN KEY (`folder`) REFERENCES `file_upload_folder` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for file_localization
-- ----------------------------
DROP TABLE IF EXISTS `file_localization`;
CREATE TABLE `file_localization` (
  `file` int(10) unsigned NOT NULL COMMENT 'Localized file.',
  `language` int(5) unsigned NOT NULL COMMENT 'Localization Language.',
  `title` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Title of file',
  `description` text COLLATE utf8_turkish_ci COMMENT 'Descripiton of file.',
  UNIQUE KEY `idxUFileLocalization` (`file`,`language`) USING BTREE,
  KEY `idxFFileLocalizationLanguage` (`language`) USING BTREE,
  CONSTRAINT `idxFLanguageOfFileLocalization` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `idxFFileOfFileLocalization` FOREIGN KEY (`file`) REFERENCES `file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci;

-- ----------------------------
-- Table structure for file_upload_folder
-- ----------------------------
DROP TABLE IF EXISTS `file_upload_folder`;
CREATE TABLE `file_upload_folder` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `name` varchar(155) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Upload folder name.',
  `url_key` varchar(255) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Folder name with special chars removed.',
  `path_absolute` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'Absolute path of the upload folder.',
  `url` text COLLATE utf8_turkish_ci NOT NULL COMMENT 'URL of the upload folder.',
  `type` char(1) COLLATE utf8_turkish_ci NOT NULL DEFAULT 'i' COMMENT 'i:internal,e:external',
  `allowed_max_size` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Max allowed file size in KB. 0=unlimited.',
  `allowed_min_size` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Min allowed file size in KB. 0=unlimited.',
  `allowed_max_width` decimal(5,2) DEFAULT '0.00' COMMENT 'Max allowed width in pixels. 0=unlimited.',
  `allowed_min_width` decimal(5,2) DEFAULT '0.00' COMMENT 'Min allowed width in pixels. 0=unlimited.',
  `allowed_max_height` decimal(5,2) DEFAULT '0.00' COMMENT 'Max allowed height in pixels. 0=unlimited.',
  `allowed_min_height` decimal(5,2) DEFAULT '0.00' COMMENT 'Min allowed height in pixels. 0=unlimited.',
  `site` int(10) unsigned DEFAULT NULL COMMENT 'Associated site.',
  `count_files` int(10) NOT NULL COMMENT 'Count of files associated with this folder.',
  `date_added` datetime NOT NULL COMMENT 'Date when the entry is first added.',
  `date_updated` datetime NOT NULL COMMENT 'Date when the entry is last updated.',
  `date_removed` datetime DEFAULT NULL COMMENT 'Date when the entry s marked as removed.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUFileUploadFolderId` (`id`) USING BTREE,
  UNIQUE KEY `idxUFileUploadFolderUrlKey` (`url_key`,`site`) USING BTREE,
  KEY `idxFSiteOfFileUploadFolder` (`site`) USING BTREE,
  KEY `idxNFileUploadFolderDateAdded` (`date_added`),
  KEY `idxNFileUploadFolderDateUpdated` (`date_updated`),
  KEY `idxNFileUploadFolderDateRemoved` (`date_removed`),
  CONSTRAINT `idxFSiteOfFileUploadFolder` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;
