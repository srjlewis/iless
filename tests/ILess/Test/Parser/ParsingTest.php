<?php

/*
 * This file is part of the ILess
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Parsing tests
 *
 * @package ILess
 * @subpackage test
 * @covers ILess_Parser_Core
 */
class ILess_Test_Parser_ParsingTest extends ILess_Test_TestCase
{
    public function setUp($options = array())
    {
        $env = new ILess_Environment($options, new ILess_FunctionRegistry());
        $importer = new ILess_Importer($env, array(
            new ILess_Importer_FileSystem()
        ), new ILess_Cache_None());
        $this->parser = new ILess_Test_Parser_Core($env, $importer);
    }

    public function testSimpleCompilation()
    {
        $less = glob(dirname(__FILE__) . '/_fixtures/simple/less/*.less');
        $css = glob(dirname(__FILE__) . '/_fixtures/simple/css/*.css');

        foreach ($less as $i => $lessFile) {
            $this->setUp();
            $this->parser->parseFile($lessFile);
            $preCompiled = file_get_contents($css[$i]);
            $this->assertEquals($preCompiled, $this->parser->getCSS(), sprintf('Testing compilation for %s', basename($lessFile)));
        }
    }

    public function testCompilation()
    {
        $fixturesDir = dirname(__FILE__) . '/_fixtures';
        $less = glob($fixturesDir . '/less.js/less/*.less');
        $css = glob($fixturesDir . '/less.js/css/*.css');

        // skip
        $skip = array();

        foreach ($less as $i => $lessFile) {
            if (in_array(basename($lessFile), $skip)) {
                $this->diag('Skipping test ' . basename($lessFile));
                continue;
            }

            // reset the parser for each test
            $this->setup();

            $this->parser->parseFile($lessFile);
            $compiled = $this->parser->getCss();

            $preCompiled = file_get_contents($css[$i]);

            // known diff, check of the diff is still ok
            if (is_readable($diffFile = $fixturesDir . '/less.js/diff/' . basename($lessFile) . '.php')) {
                // FIXME: check the diff
                $diff = include $diffFile;
                $actualDiff = array_diff(explode("\n", $compiled), explode("\n", $preCompiled));
                $this->assertEquals($diff, $actualDiff);
            } else {
                $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s"', basename($lessFile)));
            }
        }
    }

    public function testPhpCompilation()
    {
        $less = glob(dirname(__FILE__) . '/_fixtures/php/less/*.less');
        $css = glob(dirname(__FILE__) . '/_fixtures/php/css/*.css');

        foreach ($less as $i => $lessFile) {
            // reset the parser for each test
            $this->setup();

            $this->parser->setVariables(array(
                'a' => 'black',
                'fontdir' => '/fonts',
                'base' => '12px',
                'myurl' => '"http://example.com/image.jpg"'
            ));

            $this->parser->parseFile($lessFile);
            $compiled = $this->parser->getCss();
            $preCompiled = file_get_contents($css[$i]);

            // $this->diag(sprintf('Testing compilation for %s', basename($lessFile)));
            $this->assertSame(addslashes($preCompiled), addslashes($compiled), sprintf('Compiled CSS is ok for "%s".', basename($lessFile)));
        }
    }

    public function testBootstrap2Compilation() {

        $fixturesDir = dirname(__FILE__) . '/_fixtures/bootstrap2';
        $less = array($fixturesDir . '/less/bootstrap.less');
        $css = array($fixturesDir . '/css/bootstrap.css');

        foreach ($less as $i => $lessFile) {

            // reset the parser for each test
            $this->setup();

            $this->parser->parseFile($lessFile);
            $compiled = $this->parser->getCss();

            $preCompiled = file_get_contents($css[$i]);

            // known diff, check of the diff is still ok
            if (is_readable($diffFile = $fixturesDir . '/diff/' . basename($lessFile) . '.php')) {
                $diff = include $diffFile;
                $actualDiff = array_diff(explode("\n", $compiled), explode("\n", $preCompiled));
                $this->assertEquals($diff, $actualDiff);
            } else {
                $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s"', basename($lessFile)));
            }
        }
    }

    public function testBootstrap3Compilation() {

        $fixturesDir = dirname(__FILE__) . '/_fixtures/bootstrap3';
        $less = array($fixturesDir . '/less/bootstrap.less');
        $css = array($fixturesDir . '/css/bootstrap.css');

        foreach ($less as $i => $lessFile) {

            // reset the parser for each test
            $this->setup();

            $this->parser->parseFile($lessFile);
            $compiled = $this->parser->getCss();

            $preCompiled = file_get_contents($css[$i]);

            // known diff, check of the diff is still ok
            if (is_readable($diffFile = $fixturesDir . '/diff/' . basename($lessFile) . '.php')) {
                $diff = include $diffFile;
                $actualDiff = array_diff(explode("\n", $compiled), explode("\n", $preCompiled));
                $this->assertEquals($diff, $actualDiff);
            } else {
                $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s"', basename($lessFile)));
            }
        }
    }

    public function testDebugCompilation() {

        $fixturesDir = dirname(__FILE__) . '/_fixtures/less.js';
        $lessFile = $fixturesDir . '/less/debug/linenumbers.less';

        // for replacement
        $importDir = $fixturesDir . '/less/debug/import/';
        $lessDir = $fixturesDir . '/less/debug/';

        // format "all"
        $this->setUp(array(
            'dumpLineNumbers' => ILess_DebugInfo::FORMAT_ALL
        ));

        $this->parser->parseFile($lessFile);
        $compiled = $this->parser->getCSS();

        $preCompiled = file_get_contents($fixturesDir . '/css/debug/linenumbers-all.css');
        $compiled = $this->normalizePaths($compiled, $importDir, $lessDir);

        $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s" and dumpLineNumbers with "all" option', basename($lessFile)));

        // format "comment"
        $this->setUp(array(
            'dumpLineNumbers' => ILess_DebugInfo::FORMAT_COMMENT
        ));

        $this->parser->parseFile($lessFile);
        $compiled = $this->parser->getCSS();

        $preCompiled = file_get_contents($fixturesDir . '/css/debug/linenumbers-comments.css');
        $compiled = $this->normalizePaths($compiled, $importDir, $lessDir);

        $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s" and dumpLineNumbers with "comments" option', basename($lessFile)));

        // format "mediaquery"
        $this->setUp(array(
            'dumpLineNumbers' => ILess_DebugInfo::FORMAT_MEDIA_QUERY
        ));

        $this->parser->parseFile($lessFile);
        $compiled = $this->parser->getCSS();

        $preCompiled = file_get_contents($fixturesDir . '/css/debug/linenumbers-mediaquery.css');
        $compiled = $this->normalizePaths($compiled, $importDir, $lessDir);

        $this->assertEquals($preCompiled, $compiled, sprintf('Compiled CSS matches for "%s" and dumpLineNumbers with "comments" option', basename($lessFile)));
    }

    protected function normalizePaths($css, $importPath, $lessPath)
    {
        $importPath = str_replace('\\', '/', $importPath);
        $lessPath = str_replace('\\', '/', $lessPath);
        return str_replace(array(
            $importPath,
            ILess_DebugInfo::escapeFilenameForMediaQuery($importPath),
            $lessPath,
            ILess_DebugInfo::escapeFilenameForMediaQuery($lessPath)
        ), array(
            '{pathimport}',
            '{pathimportesc}',
            '{path}',
            '{pathesc}',
        ), $css);
    }

}
