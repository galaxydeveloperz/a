<?php
if (!defined('UPGRADE_RUN')) { exit; }

/* UPGRADE - FAQ
------------------------------------------------------*/

mswUpLog('Beginning F.A.Q updates < v3.0', 'instruction');

if (mswCheckTable('kbase') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "kbase` rename to `" . DB_PREFIX . "faq`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'kbase', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Rename');
  } else {
    mswUpLog('Column rename: kbase to faq', 'instruction');
  }
}

if (mswCheckColumn('faq', 'enFaq') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `enFaq` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: enFaq', 'instruction');
  }
}

if (mswCheckColumn('faq', 'orderBy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `orderBy` int(5) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: orderBy', 'instruction');
    $query = mswSQL_query("update `" . DB_PREFIX . "faq` set `orderBy` = `id` where `orderBy` = '0'");
    if ($query === 'err') {
      $ERR      = mswSQL_error(true);
      mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
    } else {
      mswUpLog('Column updated in faq: orderBy', 'instruction');
    }
  }
}

if (mswCheckColumn('faq', 'ts') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `ts` int(30) not null default '0' after `id`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: ts', 'instruction');
  }
}

if (mswCheckColumn('faq', 'addDate') == 'yes') {
  $query = mswSQL_query("update `" . DB_PREFIX . "faq` set `ts` = UNIX_TIMESTAMP(CONCAT(addDate,' 00:00:00'))");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in faq: ts', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop column `addDate`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column removed from faq: addDate', 'instruction');
  }
}

mswUpLog('Beginning F.A.Q updates v3.0+', 'instruction');

if (mswCheckTable('faqattassign') == 'yes') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqattassign` rename to `" . DB_PREFIX . "faqassign`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqattassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Rename');
  } else {
    mswUpLog('Column rename: faqattassign to faqassign', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqassign` change column `item` `itemID` int(7) not null default '0' after `question`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Change');
  } else {
    mswUpLog('Column changed in faqassign: item', 'instruction');
  }
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqassign` add column `desc` varchar(20) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faqassign: desc', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "faqassign` set `desc` = 'attachment' where `desc` = ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in faqassign: desc', 'instruction');
  }
  $query = mswSQL_query("drop table `" . DB_PREFIX . "faqattassign`");
  if ($query === 'err') {
    $ERR = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Table Drop');
  } else {
    mswUpLog('Table dropped: faqattassign', 'instruction');
  }
}

if (mswCheckColumn('faq', 'category') == 'yes') {
  $q = mswSQL_query("select `id`,`category` from `" . DB_PREFIX . "faq` order by `id`");
  while ($F = mswSQL_fetchobj($q)) {
    // All categories..
    if (in_array($F->category, array(
      '',
      '0',
      0,
      'all'
      ))) {
      $q2 = mswSQL_query("select `id` from `" . DB_PREFIX . "categories` order by `id`");
      while ($C = mswSQL_fetchobj($q2)) {
        $query = mswSQL_query("insert into `" . DB_PREFIX . "faqassign` (
        `question`,`itemID`,`desc`
        ) values (
        '{$F->id}','{$C->id}','category'
        )");
        if ($query === 'err') {
          $ERR      = mswSQL_error(true);
          mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Insert');
        } else {
          mswUpLog('Data inserted into faqassign: all fields', 'instruction');
        }
      }
    } else {
      $pa = explode(',', $F->category);
      if (!empty($pa)) {
        foreach ($pa AS $uap) {
          $query = mswSQL_query("insert into `" . DB_PREFIX . "faqassign` (
          `question`,`itemID`,`desc`
          ) values (
          '{$F->id}','{$uap}','category'
          )");
          if ($query === 'err') {
            $ERR      = mswSQL_error(true);
            mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Insert');
          } else {
            mswUpLog('Data inserted into faqassign: all fields', 'instruction');
          }
        }
      }
    }
  }

  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` drop `category`");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column dropped from faq: category', 'instruction');
  }

}

if (mswCheckColumn('faqattach', 'orderBy') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqattach` add column `orderBy` int(8) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqattach', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Drop');
  } else {
    mswUpLog('Column added to faqattach: orderBy', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "faqattach` set `orderBy` = `id` WHERE `orderBy` = '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqattach', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in faqattach: orderBy', 'instruction');
  }
}

if (mswCheckColumn('faqattach', 'enAtt') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqattach` add column `enAtt` enum('yes','no') not null default 'yes'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqattach', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faqattach: enAtt', 'instruction');
  }
}

if (mswCheckColumn('faqattach', 'mimeType') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faqattach` add column `mimeType` varchar(100) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqattach', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faqattach: mimeType', 'instruction');
  }
}

mswUpLog('Beginning F.A.Q updates v3.0+', 'instruction');

if (mswCheckColumn('faq', 'featured') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `featured` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: featured', 'instruction');
  }
}

if (mswCheckColumn('faq', 'private') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `private` enum('yes','no') not null default 'no'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: private', 'instruction');
  }
}

mswUpLog('Beginning F.A.Q updates 4.0', 'instruction');

if (mswCheckColumn('faq', 'cat') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `cat` int(7) not null default '0'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: cat', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "faq` set `cat` = (select `itemid` from `" . DB_PREFIX . "faqassign` where `question` = `" . DB_PREFIX . "faq`.`id` and `desc` = 'category' order by `question` asc limit 1) where `cat` = 0");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in faq: cat', 'instruction');
  }
  $query = mswSQL_query("update `" . DB_PREFIX . "faq` set `private` = (select `private` from `" . DB_PREFIX . "categories` where `id` = `" . DB_PREFIX . "faq`.`cat`)");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Update');
  } else {
    mswUpLog('Column updated in faq: private', 'instruction');
  }
  $query = mswSQL_query("delete from `" . DB_PREFIX . "faqassign` where `desc` = 'category'");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faqassign', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Column Delete');
  } else {
    mswUpLog('Data deletion in faqassign for desc', 'instruction');
  }
}

if (mswCheckColumn('faq', 'tmp') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `tmp` varchar(250) not null default ''");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: tmp', 'instruction');
  }
}

if (mswCheckColumn('faq', 'searchkeys') == 'no') {
  $query = mswSQL_query("alter table `" . DB_PREFIX . "faq` add column `searchkeys` text default null");
  if ($query === 'err') {
    $ERR      = mswSQL_error(true);
    mswUpLog(DB_PREFIX . 'faq', $ERR[1], $ERR[0], __LINE__, __FILE__, 'Add Column');
  } else {
    mswUpLog('Column added to faq: searchkeys', 'instruction');
  }
}

mswUpLog('F.A.Q upgrades completed', 'instruction');

?>