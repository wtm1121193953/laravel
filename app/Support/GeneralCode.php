<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 22:55
 */

namespace App\Support;


use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

trait GeneralCode
{

    /**
     * Find stubs.
     *
     * @param string $stubPath
     * @return \Symfony\Component\Finder\Finder
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function findStub($stubPath): Finder
    {
        $finder = new Finder();

        return $finder->files()
            ->in($stubPath)
            ->ignoreVCS(false)
            ->ignoreDotFiles(false);
    }

    /**
     * Put stub.
     *
     * @param \Symfony\Component\Finder\Finder $stubs
     * @param string $outputPath
     * @param array $variable
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function putStub(Finder $stubs, string $outputPath, array $variable = [])
    {
        $this->getOutput()->progressStart($stubs->count());
        foreach ($stubs as $file) {
            $content = $file->getContents();

            $filenameDir = $outputPath.'/'.$file->getRelativePath();
            $filename = $filenameDir.'/'.$file->getBasename();
            if ($file->getExtension() === 'stub') {
                $filename = $filenameDir.'/'.$file->getBasename('.stub');
            }

            foreach ($variable as $key => $value) {
                $content = str_replace($key, $value, $content);
                $filename = str_replace($key, $value, $filename);
                $filenameDir = str_replace($key, $value, $filenameDir);
            }

            $filename = str_replace('\\', '/', $filename);
            $filenameDir = str_replace('\\', '/', $filenameDir);
            if (! file_exists($filenameDir)) {
                mkdir($filenameDir, 0777, true);
            }

            file_put_contents($filename, $content);
            $this->getOutput()->progressAdvance(1);
        }

        $this->getOutput()->progressFinish();
    }

    protected function throwFileOrDirExistException($fileIntro){
        throw new RuntimeException("{$fileIntro}已存在, 停止生成文件, 可使用 --force 选项强制覆盖文件");
    }

}