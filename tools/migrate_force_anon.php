<?php
require dirname(__FILE__) . '/inc/cli.php';

$boards = listBoards(TRUE);

foreach ($boards as $i => $b) {
  echo "Processing board $b...\n";
  query(sprintf('ALTER TABLE ``posts_%s`` ADD COLUMN force_anon BOOLEAN DEFAULT FALSE AFTER sage', $b));
}
