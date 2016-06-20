<?php

class YamlConfig {

  // result of parse config
  protected $cArr;
  protected $env;

  public function __construct ($config,$env) {
    $this->cArr = yaml_parse_file($config);
    $this->env = $env;
  }

  public function getIncludeObjects ($env='default') {
    // return array of included objects or False (if default not set)
    $arr = $this->cArr['stagings'];
    $out = [];
    if (array_key_exists($env,$arr) && count($arr[$env]['include']) > 0) {
      foreach ($arr[$env]['include'] as $key => $value) {
        array_push($out,$value);
      }
    } else if (array_key_exists('default',$arr) && count($arr['default']['include']) > 0) {
      foreach ($arr['default']['include'] as $key => $value) {
        array_push($out,$value);
      }
    } else {
      $out = False;
    }
    return $out;
  }

  public function getExcludeObjects($env='default') {
    // return array of included objects or False (if default not set)
    $arr = $this->cArr['stagings'];
    $out = [];
    if (array_key_exists($env,$arr) && count($arr[$env]['exclude']) > 0) {
      foreach ($arr[$env]['exclude'] as $key => $value) {
        array_push($out,$value);
      }
    } else if (array_key_exists('default',$arr) && count($arr['default']['exclude']) > 0) {
      foreach ($arr['default']['exclude'] as $key => $value) {
        array_push($out,$value);
      }
    } else {
      $out = False;
    }
    return $out;
  }

  public function getServerList() {
    $arr = $this->cArr['servers'];
    if (count($arr) > 0) {
      return $arr;
    }
  }

  public function getProjectPath() {
    return $this->cArr['project_path'];
  }

  public function getReleaseName() {
    return $this->cArr['release_name'];
  }

  private function arrayToRegexp($arr) {
    if (is_array($arr)) {
      $out = '';
      foreach ($arr as $i) {
        //$out .=$this->getProjectPath().'/'.$i.'|';
        $out .=$i.'|';
      }
      return '/'.str_replace('/','\/',substr($out,0,strlen($out)-1)).'/';
    } else {
      return false;
    }
  }

  private function folderToZip($folder, &$zipFile, $exclusiveLength,$include=false,$exclude=false) {
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
      if ($f != '.' && $f != '..') {
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);
        if (is_dir($filePath)) {
          $localPath .= '/';
        }
        if ( ($include !== false && preg_match($include, $localPath)) || ($include === false) ) {
          if ( ($exclude !== false && !preg_match($exclude, $localPath)) || ($exclude === false) ) {
            if (is_file($filePath)) {
              $zipFile->addFile($filePath, $localPath);
            } elseif (is_dir($filePath)) {
              // Add sub-directory.
              $zipFile->addEmptyDir($localPath);
              self::folderToZip($filePath, $zipFile, $exclusiveLength,$include,$exclude);
            }
          }
        }
      }
    }
    closedir($handle);
  }

  public function zipDir($outZipPath) {
    $sourcePath = self::getProjectPath();
    $sourcePath = str_replace('\\', '/', realpath($sourcePath));
    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'].'/'.$pathInfo['basename'];
    //$parentPath = $pathInfo['basename'];
    $dirName = $pathInfo['basename'];

    if (!extension_loaded('zip')) {
      exit ("Can't find ZIP extension");
    }
    $z = new ZipArchive();
    if (!$z->open($outZipPath, ZIPARCHIVE::CREATE)) {
      exit("Can't create archive");
    }
    //$z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"), self::arrayToRegexp(self::getIncludeObjects($this->env)), self::arrayToRegexp(self::getExcludeObjects($this->env)));
    $z->close();
  }
}
?>
