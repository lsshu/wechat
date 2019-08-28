<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 16:51
 */

if(!function_exists('fileCache')){
    /**
     * 读写缓存文件
     * @param string $file 文件
     * @param string|array $content 内容
     * @param string $mode 缓存方式 serialize export
     * @return bool|int
     */
    function fileCache($file, $content=null, $mode = 'serialize'){
        if($content===null){
            if(file_exists($file)){
                if($mode == 'export'){
                    return require $file ?? '';
                }
                if($mode == 'serialize'){
                    $handle=fopen($file,'r');
                    return unserialize(fread($handle,filesize($file))) ?? '';
                }
            }
            return [];

        }else{
            // 文件不可写
            if(false === fopen($file,'w+')){return false;}
            // 内容 是否为数组
            if(is_array($content)){
                if($mode == 'export'){
                    $content="<?php\n\rreturn ".var_export($content,true).';';
                }
                if($mode == 'serialize'){
                    $content = serialize($content);
                }
            }
            return file_put_contents($file,$content);//写入缓存
        }
    }
}
if(!function_exists('cacheFile')){
    /**
     * 设置或者获取缓存
     * @param $key
     * @param null $value
     * @param null $file
     * @param string $mode
     * @return bool|int|string
     */
    function cacheFile($key, $value=null, $file=null, $mode = 'serialize')
    {
        if($file === null){$file=('./tmp/cache_file.php');}

        if($value === null){// 获取文件内容
            $cache = fileCache($file,null,$mode);
            return $cache[$key] ?? '';
        }else{// 写入内容
            $cache = fileCache($file,null,$mode);
            $cache[$key] = $value;
            return fileCache($file,$cache,$mode);
        }
    }
}
if(!function_exists('cacheFileDel')){
    /**
     * 删除 缓存
     * @param $key
     * @param null $value
     * @param null $file
     * @param string $mode
     * @return bool|int|string
     */
    function cacheFileDel($key, $file=null, $mode = 'serialize')
    {
        if($file === null){$file=('./tmp/cache_file.php');}
        $cache = fileCache($file,null,$mode);
        unset($cache[$key]);
        return fileCache($file,$cache,$mode);
    }
}