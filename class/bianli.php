<?php
/** 
 * @author Administrator    php遍历目录，生成目录下每个文件的md5值并写入到结果文件中
 * 
 */
class TestGenerate {
    public static $appFolder = "";
    public static $ignoreFilePaths = array (
        "xxxx/xxx.php"
    );
    public static function start() {
        $AppPath = "E:\\ps";
        TestGenerate::$appFolder = $AppPath;
        $destManifestPath = "E:\\test.txt";
         
        // dest file handle
        $manifestHandle = fopen ( $destManifestPath, "w+" );
         
        // write header
        TestGenerate::writeMaifestHeader ( $manifestHandle );
         
        // write md5
        TestGenerate::traverse ( $AppPath, $manifestHandle );
         
        // write footer
        TestGenerate::writeMaifestFooter ( $manifestHandle );
         
        // close file
        fclose ( $manifestHandle );
    }
     
    /**
     * 遍历应用根目录下的文件，并生成对应的文件长度及md5信息
     *
     * @param unknown $AppPath
     *          应用根目录，如：xxx/xxx/analytics
     * @param string $destManifestPath
     *          生成的manifest文件存放位置的文件句柄
     */
    public static function traverse($AppPath, $manifestHandle) {
        if (! file_exists ( $AppPath )) {
            printf ( $AppPath . " does not exist!" );
            return;
        }
        if (! is_dir ( $AppPath )) {
            printf ( $AppPath . " is not a directory!" );
            return;
        }
        if (! ($dh = opendir ( $AppPath ))) {
            printf ( "Failure while read diectory!" );
            return;
        }
         
        // read files
        while ( ($file = readdir ( $dh )) != false ) {
            $subDir = $AppPath . DIRECTORY_SEPARATOR . $file;
             
            if ($file == "." || $file == "..") {
                continue;
            } else if (is_dir ( $subDir )) {
                // rescure
                TestGenerate::traverse ( $subDir, $manifestHandle );
            } else {
                // Sub is a file.
                TestGenerate::writeOneFieToManifest ( $subDir, $manifestHandle );
            }
        }
         
        // close dir
        closedir ( $dh );
    }
     
    /**
     * 写一个文件的md5信息到文件中
     *
     * @param unknown $filePath         
     * @param unknown $fileHandle           
     */
    public static function writeOneFieToManifest($filePath, $fileHandle) {
        if (! file_exists ( $filePath )) {
            continue;
        }
         
        $relativePath = str_replace ( TestGenerate::$appFolder . DIRECTORY_SEPARATOR, '', $filePath );
        $relativePath = str_replace ( "\\", "/", $relativePath );
         
        // ignore tmp directory
        if (strpos ( $relativePath, "tmp/" ) === 0) {
            return;
        }
         
        $fileSize = filesize ( $filePath );
        $fileMd5 = @md5_file ( $filePath );
         
        $content = "\t\t";
        $content .= '"';
        $content .= $relativePath;
        $content .= '"';
        $content .= ' => array("';
        $content .= $fileSize;
        $content .= '","';
        $content .= $fileMd5;
        $content .= '"),';
        $content .= "\n";
         
        if (! fwrite ( $fileHandle, $content )) {
            print ($filePath . " can not be written!") ;
        }
    }
     
    /**
     * 在manifes文件中写入头信息
     *
     * @param unknown $fileHandle           
     */
    public static function writeMaifestHeader($fileHandle) {
        $header = "<?php";
        $header .= "\n";
        $header .= "// This file is automatically generated";
        $header .= "\n";
        $header .= "namespace test;";
        $header .= "\n";
        $header .= "class MyFile {";
        $header .= "\n";
        $header .= "\tstatic \$allFiles=array(";
        $header .= "\n";
         
        if (! fwrite ( $fileHandle, $header )) {
            printf ( "Failure while write file header." );
        }
    }
     
    /**
     * 在manifes文件中写入尾部信息
     *
     * @param unknown $fileHandle           
     */
    public static function writeMaifestFooter($fileHandle) {
        $footer = "\t);";
        $footer .= "\n";
        $footer .= "}";
        $footer .= "\n";
         
        if (! fwrite ( $fileHandle, $footer )) {
            printf ( "Failure while write file header." );
        }
    }
}
 
// Start application
TestGenerate::start ();
 
?>