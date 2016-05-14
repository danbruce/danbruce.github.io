<?php

require_once __DIR__.'/vendor/autoload.php';

use zz\Html\HTMLMinify;

// ensure we can create a dist folder and it is writeable
$distPath = __DIR__.'/dist';
if (!file_exists($distPath)) {
  // try to create the dist folder
  mkdir($distPath) or die('Unable to create dist folder.'.PHP_EOL);
} else if (!is_dir($distPath) || !is_writable($distPath)) {
  die('Unable to write to dist folder.'.PHP_EOL);
}

$pagesPath = './pages';
if (!file_exists($pagesPath) || !is_dir($pagesPath)) {
  die('Missing pages folder.'.PHP_EOL);
}

// remove any files in the dist folder
exec(sprintf(
  'rm -rf %s/*',
  escapeshellcmd($distPath)
));
// copy all the files from the static folder
$staticPath = __DIR__.'/static';
exec(sprintf(
  'cp -r %s/* %s/',
  escapeshellcmd($staticPath),
  escapeshellcmd($distPath)
));

$loader = new Twig_Loader_Filesystem(__DIR__.'/pages');
$twig = new Twig_Environment($loader);

if ($handler = opendir($pagesPath)) {
  while (false !== ($pageFile = readdir($handler))) {
    if ($pageFile === '.' || $pageFile === '..') {
      continue;
    } else if (is_dir($pagesPath.'/'.$pageFile)) {
      continue;
    }
    $output = null;
    if (preg_match('/^(\S+)\.twig$/', $pageFile, $output)) {
      $outputPath = $distPath.'/'.$output[1].'.html';
      $generated = $twig->render($pageFile);
      $minified = HTMLMinify::minify($generated);
      if ($generated) {
        file_put_contents($outputPath, $minified);
      }
    }
  }

  closedir($handler);
} else {
  die('Unable to read pages folder.'.PHP_EOL);
}

echo 'Done'.PHP_EOL;
