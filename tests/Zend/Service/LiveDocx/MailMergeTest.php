<?php

/**
 * @namespace
 */
namespace Zend\Service\LiveDocx;

use Zend\Soap\Client as SoapClient;


class MailMergeTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TEMPLATE_1 = 'phpunit-template.docx';
    const TEST_TEMPLATE_2 = 'phpunit-template-block-fields.doc';

    const TEST_IMAGE_1    = 'image-01.png';
    const TEST_IMAGE_2    = 'image-02.png';

    public $_path;
    public $_mailMerge;

    // -------------------------------------------------------------------------

    public function setUp()
    {
        if (!constant('TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME') ||
                !constant('TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD')) {
            $this->markTestSkipped('LiveDocx tests disabled');
            return true;
        }
        
        $this->_mailMerge = new MailMerge();
        $this->_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME)
                         ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

        foreach($this->_mailMerge->listTemplates() as $template) {
            $this->_mailMerge->deleteTemplate($template['filename']);
        }

        $this->_path = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'MailMerge');

        return true;
    }

    public function tearDown()
    {
        if (isset($this->_mailMerge)) {
            foreach ($this->_mailMerge->listTemplates() as $template) {
                $this->_mailMerge->deleteTemplate($template['filename']);
            }
            unset($this->_mailMerge);
        }
        
        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Manually check the available methods on the backend service, so we see when
     * backend engineers introduce new methods. We need to know this, so that the
     * Zend Framework components can be updated to reflect the changes.
     */
    public function testGetSoapFunctions()
    {
        $expectedResults = array(

            /*

                1 = implemented
                0 = not implemented

                Library | Tests          (Date: May 25, 2011)

            */

            /* 1 | 1 */ 'CreateDocumentResponse CreateDocument(CreateDocument $parameters)',
            /* 1 | 1 */ 'DeleteImageResponse DeleteImage(DeleteImage $parameters)',
            /* 1 | 1 */ 'DeleteTemplateResponse DeleteTemplate(DeleteTemplate $parameters)',
            /* 1 | 1 */ 'DownloadImageResponse DownloadImage(DownloadImage $parameters)',
            /* 1 | 1 */ 'DownloadTemplateResponse DownloadTemplate(DownloadTemplate $parameters)',
            /* 1 | 1 */ 'GetAllBitmapsResponse GetAllBitmaps(GetAllBitmaps $parameters)',
            /* 1 | 1 */ 'GetAllMetafilesResponse GetAllMetafiles(GetAllMetafiles $parameters)',
            /* 1 | 1 */ 'GetBitmapsResponse GetBitmaps(GetBitmaps $parameters)',
            /* 1 | 1 */ 'GetBlockFieldNamesResponse GetBlockFieldNames(GetBlockFieldNames $parameters)',
            /* 1 | 1 */ 'GetBlockNamesResponse GetBlockNames(GetBlockNames $parameters)',
            /* 1 | 1 */ 'GetDocumentAccessOptionsResponse GetDocumentAccessOptions(GetDocumentAccessOptions $parameters)',
            /* 1 | 1 */ 'GetDocumentFormatsResponse GetDocumentFormats(GetDocumentFormats $parameters)',
            /* 1 | 1 */ 'GetFieldNamesResponse GetFieldNames(GetFieldNames $parameters)',
            /* 1 | 1 */ 'GetFontNamesResponse GetFontNames(GetFontNames $parameters)',
            /* 1 | 1 */ 'GetImageExportFormatsResponse GetImageExportFormats(GetImageExportFormats $parameters)',
            /* 1 | 1 */ 'GetImageImportFormatsResponse GetImageImportFormats(GetImageImportFormats $parameters)',
            /* 1 | 1 */ 'GetMetafilesResponse GetMetafiles(GetMetafiles $parameters)',
            /* 1 | 1 */ 'GetTemplateFormatsResponse GetTemplateFormats(GetTemplateFormats $parameters)',
            /* 1 | 1 */ 'ImageExistsResponse ImageExists(ImageExists $parameters)',
            /* 1 | 1 */ 'ListImagesResponse ListImages(ListImages $parameters)',
            /* 1 | 1 */ 'ListTemplatesResponse ListTemplates(ListTemplates $parameters)',
            /* 1 | 1 */ 'LogInResponse LogIn(LogIn $parameters)',
            /* 1 | 1 */ 'LogOutResponse LogOut(LogOut $parameters)',
            /* 1 | 1 */ 'RetrieveDocumentResponse RetrieveDocument(RetrieveDocument $parameters)',
            /* 1 | 1 */ 'SetBlockFieldValuesResponse SetBlockFieldValues(SetBlockFieldValues $parameters)',
            /* 1 | 1 */ 'SetDocumentAccessPermissionsResponse SetDocumentAccessPermissions(SetDocumentAccessPermissions $parameters)',
            /* 1 | 1 */ 'SetDocumentPasswordResponse SetDocumentPassword(SetDocumentPassword $parameters)',
            /* 1 | 1 */ 'SetFieldValuesResponse SetFieldValues(SetFieldValues $parameters)',
            /* 1 | 1 */ 'SetLocalTemplateResponse SetLocalTemplate(SetLocalTemplate $parameters)',
            /* 1 | 1 */ 'SetRemoteTemplateResponse SetRemoteTemplate(SetRemoteTemplate $parameters)',
            /* 1 | 1 */ 'TemplateExistsResponse TemplateExists(TemplateExists $parameters)',
            /* 1 | 1 */ 'UploadImageResponse UploadImage(UploadImage $parameters)',
            /* 1 | 1 */ 'UploadTemplateResponse UploadTemplate(UploadTemplate $parameters)',

            /* 0 | 0 */ 'SetIgnoreSubTemplatesResponse SetIgnoreSubTemplates(SetIgnoreSubTemplates $parameters)',
            /* 0 | 0 */ 'SetSubTemplateIgnoreListResponse SetSubTemplateIgnoreList(SetSubTemplateIgnoreList $parameters)',

        );

        $expectedResults = array_unique($expectedResults);

        sort($expectedResults);

        $soapClient = new \SoapClient($this->_mailMerge->getWsdl());

        $actualResults = array_unique($soapClient->__getFunctions());

        sort($actualResults);

        foreach ($actualResults as $key => $value) {
            $this->assertEquals($expectedResults[$key], $actualResults[$key]);
        }

        unset($soapClient);
    }
    
    // -------------------------------------------------------------------------

    public function testGetFormat()
    {
        $this->assertNotEquals('docx',  $this->_mailMerge->getFormat('document.doc'));
        $this->assertNotEquals('docx',  $this->_mailMerge->getFormat('document-123.doc'));
        $this->assertNotEquals('docx',  $this->_mailMerge->getFormat('document123.doc'));
        $this->assertNotEquals('docx',  $this->_mailMerge->getFormat('document.123.doc'));
        
        $this->assertEquals('docx', $this->_mailMerge->getFormat('document.docx'));
        $this->assertEquals('docx', $this->_mailMerge->getFormat('document-123.docx'));
        $this->assertEquals('docx', $this->_mailMerge->getFormat('document123.docx'));
        $this->assertEquals('docx', $this->_mailMerge->getFormat('document.123.docx'));
        
        $this->assertEquals('doc',  $this->_mailMerge->getFormat('document.doc'));
        $this->assertEquals('doc',  $this->_mailMerge->getFormat('document-123.doc'));
        $this->assertEquals('doc',  $this->_mailMerge->getFormat('document123.doc'));
        $this->assertEquals('doc',  $this->_mailMerge->getFormat('document.123.doc'));

        $this->assertEquals('rtf',  $this->_mailMerge->getFormat('document.rtf'));
        $this->assertEquals('rtf',  $this->_mailMerge->getFormat('document-123.rtf'));
        $this->assertEquals('rtf',  $this->_mailMerge->getFormat('document123.rtf'));
        $this->assertEquals('rtf',  $this->_mailMerge->getFormat('document.123.rtf'));

        $this->assertEquals('txt',  $this->_mailMerge->getFormat('document.txt'));
        $this->assertEquals('txt',  $this->_mailMerge->getFormat('document-123.txt'));
        $this->assertEquals('txt',  $this->_mailMerge->getFormat('document123.txt'));
        $this->assertEquals('txt',  $this->_mailMerge->getFormat('document.123.txt'));

        $this->assertEquals('htm',  $this->_mailMerge->getFormat('document.htm'));
        $this->assertEquals('htm',  $this->_mailMerge->getFormat('document-123.htm'));
        $this->assertEquals('htm',  $this->_mailMerge->getFormat('document123.htm'));
        $this->assertEquals('htm',  $this->_mailMerge->getFormat('document.123.htm'));

        $this->assertEquals('',     $this->_mailMerge->getFormat('document'));
    }

    public function testGetVersion()
    {
        $this->assertEquals('2.0', $this->_mailMerge->getVersion());
    }

    public function testGetSoapClient()
    {
        $this->assertTrue(is_a($this->_mailMerge->getSoapClient(), '\Zend\Soap\Client'));
    }

    public function testSetUsernameGetUsername()
    {
        $username = 'invalid-username';

        $this->assertTrue(is_a($this->_mailMerge->setUsername($username), '\Zend\Service\LiveDocx\MailMerge'));

        $this->assertEquals($username, $this->_mailMerge->getUsername());
    }

    public function testSetPasswordGetPassword()
    {
        $password = 'invalid-password';

        $this->assertTrue(is_a($this->_mailMerge->setPassword($password), '\Zend\Service\LiveDocx\MailMerge'));

        $this->assertEquals($password, $this->_mailMerge->getPassword());
    }

    public function testSetWsdlGetWsdl()
    {
        $wsdl = 'http://example.com/somewhere.wsdl';

        $_mailMerge = new MailMerge();
        $_mailMerge->setWsdl($wsdl);

        $this->assertTrue(is_a($_mailMerge->setWsdl($wsdl), '\Zend\Service\LiveDocx\MailMerge'));
        
        $this->assertEquals($wsdl, $_mailMerge->getWsdl());

        unset($_mailMerge);
    }

    public function testSetWsdlGetWsdlWithSoapClient()
    {
        $wsdl = 'http://example.com/somewhere.wsdl';

        $_mailMerge = new MailMerge();

        $soapClient = new \Zend\Soap\Client();
        $soapClient->setWsdl($wsdl);

        $this->assertTrue(is_a($_mailMerge->setSoapClient($soapClient), '\Zend\Service\LiveDocx\MailMerge'));

        $this->assertEquals($wsdl, $_mailMerge->getWsdl());

        unset($_mailMerge);
    }

    public function testSetOptionsGetOptions()
    {
        $options = array (
            'username' => 'invalid-username',
            'password' => 'invalid-password',
            'wsdl'     => 'http://example.com/somewhere.wsdl',
        );

        $this->assertTrue(is_a($this->_mailMerge->setOptions($options), '\Zend\Service\LiveDocx\MailMerge'));
    }

    // -------------------------------------------------------------------------

    public function testInvalidSetOptions()
    {
        $options = array(
            'username'       => 'invalid-username',
            'password'       => 'invalid-password',
            'wsdl'           => 'http://example.com/somewhere.wsdl',
            'invalid-option' => 'invalid-option'
        );

        try {
            $this->_mailMerge->setOptions($options);
            $this->fail('exception expected');
        } catch (Exception\InvalidArgumentException $e) {}
    }

    public function testMissingUsername()
    {
        
        $_mailMerge = new MailMerge();

        try {
            $_mailMerge->listTemplates();
            $this->fail('exception expected');
        } catch (Exception\InvalidArgumentException $e) {}

        unset($_mailMerge);
    }

    public function testMissingPassword()
    {
        $_mailMerge = new MailMerge();
        $_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME);

        try {
            $_mailMerge->listTemplates();
            $this->fail('exception expected');
        } catch (Exception\InvalidArgumentException $e) {}

        unset($_mailMerge);
    }

    public function testInvalidCredentials()
    {
        $_mailMerge = new MailMerge();
        $_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME)
                   ->setPassword('invalid-password');
        
        try {
            $_mailMerge->listTemplates();
            $this->fail('exception expected');
        } catch (\Zend\Service\LiveDocx\Exception\RuntimeException $e) {}

        unset($_mailMerge);

        $_mailMerge = new MailMerge();
        $_mailMerge->setUsername('invalid-username')
                   ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

        try {
            $_mailMerge->listTemplates();
            $this->fail('exception expected');
        } catch (\Zend\Service\LiveDocx\Exception\RuntimeException $e) {}

        unset($_mailMerge);
    }

    public function testWsdlHttpFileNotFound()
    {
        $_mailMerge = new MailMerge();
        $_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME)
                   ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD)
                   ->setWsdl('http://www.livedocx.com/file-not-found.wsdl');

        try {
            $_mailMerge->listTemplates();
            $this->fail('exception expected');
        } catch (\Zend\Service\LiveDocx\Exception\RuntimeException $e) {}

        unset($_mailMerge);
    }

    // -------------------------------------------------------------------------
    
    public function testSetLocalTemplateMissingOnLocalFileSystem()
    {
        try {
            $this->_mailMerge->setLocalTemplate('invalid-template'); 
            $this->fail('exception expected');
        } catch (Exception\InvalidArgumentException $e) {}
    }

    public function testSetRemoteTemplateMissingOnRemoteFileSystem()
    {
        try {
            $this->_mailMerge->setRemoteTemplate('invalid-template');
            $this->fail('exception expected');
        } catch (\Zend\Service\LiveDocx\Exception\RuntimeException $e) {}
    }

    public function testUploadTemplateMissingOnLocalFileSystem()
    {
        try {
            $this->_mailMerge->uploadTemplate('invalid-template');
            $this->fail('exception expected');
        } catch (Exception\InvalidArgumentException $e) {}
    }

    // -------------------------------------------------------------------------

    public function testLoginWithSetUsernameSetPassword()
    {
        $_mailMerge = new MailMerge();

        $_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME)
                   ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD);

        $this->assertTrue(is_a($this->_mailMerge->setLocalTemplate($this->_path .
                DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));

        unset($_mailMerge);
    }

    public function testLoginWithSetUsernameSetPasswordSoapClient()
    {
        $_mailMerge = new MailMerge();

        $_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME)
                   ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD)
                   ->setSoapClient(new SoapClient($this->_mailMerge->getWsdl()));

        $this->assertTrue(is_a($this->_mailMerge->setLocalTemplate($this->_path .
                DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));

        unset($_mailMerge);
    }

    public function testLoginWithConstructorOptionsUsernamePassword()
    {
        $_mailMerge = new MailMerge(
            array (
                'username' => TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME,
                'password' => TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD
            )
        );

        $this->assertTrue(is_a($this->_mailMerge->setLocalTemplate($this->_path .
                DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));

        unset($_mailMerge);
    }

    public function testLoginWithConstructorOptionsUsernamePasswordSoapClient()
    {
        $_mailMerge = new MailMerge(
            array (
                'username'   => TESTS_ZEND_SERVICE_LIVEDOCX_USERNAME,
                'password'   => TESTS_ZEND_SERVICE_LIVEDOCX_PASSWORD,
                'soapClient' => new SoapClient($this->_mailMerge->getWsdl())
            )
        );

        $this->assertTrue(is_a($this->_mailMerge->setLocalTemplate($this->_path .
                DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));

        unset($_mailMerge);
    }

    // -------------------------------------------------------------------------

    public function testSetLocalTemplate()
    {
        $this->assertTrue(is_a($this->_mailMerge->setLocalTemplate($this->_path .
                DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));
        $this->setExpectedException('\Zend\Service\LiveDocx\Exception');
        @$this->_mailMerge->setLocalTemplate('phpunit-nonexistent.doc');
    }

    public function testSetRemoteTemplate()
    {
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_1), '\Zend\Service\LiveDocx\MailMerge'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);
    }
    
    public function testSetFieldValues()
    {
        $testValues = array('software' => 'phpunit');

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->setFieldValues($testValues), '\Zend\Service\LiveDocx\MailMerge'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->setFieldValues($testValues), '\Zend\Service\LiveDocx\MailMerge'));
    }

    public function testSetFieldValue()
    {
        $testKey   = 'software';
        $testValue = 'phpunit';

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->setFieldValue($testKey, $testValue), '\Zend\Service\LiveDocx\MailMerge'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->setFieldValue($testKey, $testValue), '\Zend\Service\LiveDocx\MailMerge'));
    }

    public function testAssign()
    {
        $testKey   = 'software';
        $testValue = 'phpunit';

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->assign($testKey, $testValue), '\Zend\Service\LiveDocx\MailMerge'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->assertTrue(is_a($this->_mailMerge->assign($testKey, $testValue), '\Zend\Service\LiveDocx\MailMerge'));
    }

    public function testSetBlockFieldValues()
    {
        $testKey    = 'connection';
        $testValues = array(array('connection_number' => 'unittest', 'connection_duration' => 'unittest', 'fee' => 'unittest'),
                            array('connection_number' => 'unittest', 'connection_duration' => 'unittest', 'fee' => 'unittest'),
                            array('connection_number' => 'unittest', 'connection_duration' => 'unittest', 'fee' => 'unittest'),
                            array('connection_number' => 'unittest', 'connection_duration' => 'unittest', 'fee' => 'unittest') );

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertTrue(is_a($this->_mailMerge->setBlockFieldValues($testKey, $testValues), '\Zend\Service\LiveDocx\MailMerge'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertTrue(is_a($this->_mailMerge->setBlockFieldValues($testKey, $testValues), '\Zend\Service\LiveDocx\MailMerge'));
    }

    // -------------------------------------------------------------------------

    public function testCreateDocument()
    {
        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->assertNull($this->_mailMerge->createDocument());
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->assertNull($this->_mailMerge->createDocument());
    }

    public function testRetrieveDocument()
    {
        $formats = array('doc', 'docx', 'html', 'pdf', 'rtf', 'txd', 'txt');

        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach ($formats as $format) {
            $document = $this->_mailMerge->retrieveDocument($format);
            $this->assertGreaterThan(2048, strlen($document));
        }
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach ($formats as $format) {
            $document = $this->_mailMerge->retrieveDocument($format);
            $this->assertGreaterThan(2048, strlen($document));
        }
    }

    public function testRetrieveDocumentAppended()
    {
        $formats = array('doc', 'docx', 'html', 'pdf', 'rtf', 'txd', 'txt');

        $testValues = array(
            array(
                'software' => 'phpunit - document 1',
                'licensee' => 'phpunit - document 1',
                'company'  => 'phpunit - document 1',
                'date'     => 'phpunit - document 1',
                'time'     => 'phpunit - document 1',
                'city'     => 'phpunit - document 1',
                'country'  => 'phpunit - document 1'
            ),
            array(
                'software' => 'phpunit - document 2',
                'licensee' => 'phpunit - document 2',
                'company'  => 'phpunit - document 2',
                'date'     => 'phpunit - document 2',
                'time'     => 'phpunit - document 2',
                'city'     => 'phpunit - document 2',
                'country'  => 'phpunit - document 2'
            ),
        );

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach ($formats as $format) {
            $document = $this->_mailMerge->retrieveDocument($format);
            $this->assertGreaterThan(2048, strlen($document));
        }
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach ($formats as $format) {
            $document = $this->_mailMerge->retrieveDocument($format);
            $this->assertGreaterThan(2048, strlen($document));
        }
    }

    // -------------------------------------------------------------------------

    public function testGetTemplateFormats()
    {
        $expectedResults = array('doc' , 'docx' , 'rtf' , 'txd');
        $this->assertEquals($expectedResults, $this->_mailMerge->getTemplateFormats());
    }

    public function testGetDocumentFormats()
    {
        $expectedResults = array('doc' , 'docx' , 'html' , 'pdf' , 'rtf' , 'txd' , 'txt');
        $this->assertEquals($expectedResults, $this->_mailMerge->getDocumentFormats());
    }

    public function testGetImageImportFormats()
    {
        $expectedResults = array('bmp' , 'gif' , 'jpg' , 'png' , 'tiff', 'wmf');
        $this->assertEquals($expectedResults, $this->_mailMerge->getImageImportFormats());
    }

    public function testGetImageExportFormats()
    {
        $expectedResults = array('bmp' , 'gif' , 'jpg' , 'png' , 'tiff');
        $this->assertEquals($expectedResults, $this->_mailMerge->getImageExportFormats());
    }

    // -------------------------------------------------------------------------

    public function testGetBitmaps()
    {
        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $expectedResults = array(
            'bmp'  => 'a1934f2153172f021847af7ece9049ce',
            'gif'  => 'd7281d7b6352ff897917e25d6b92746f',
            'jpg'  => 'e0b20ea2c9a6252886f689f227109085',
            'png'  => 'c449f0c2726f869e9a42156e366f1bf9',
            'tiff' => '20a96a94762a531e9879db0aa6bd673f'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach($this->_mailMerge->getImageExportFormats() as $format) {
            $bitmaps = $this->_mailMerge->getBitmaps(1, 1, 20, $format);
            $this->assertEquals($expectedResults[$format], md5(serialize($bitmaps)));
        }
    }

    public function testGetAllBitmaps()
    {
        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $expectedResults = array(
            'bmp'  => 'e8a884ee61c394deec8520fb397d1cf1',
            'gif'  => '2255fee47b4af8438b109efc3cb0d304',
            'jpg'  => 'e1acfc3001fc62567de2a489eccdb552',
            'png'  => '15eac34d08e602cde042862b467fa865',
            'tiff' => '98bad79380a80c9cc43dfffc5158d0f9'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();
        foreach($this->_mailMerge->getImageExportFormats() as $format) {
            $bitmaps = $this->_mailMerge->getAllBitmaps(20, $format);
            $this->assertEquals($expectedResults[$format], md5(serialize($bitmaps)));
        }
    }

    public function testGetMetafiles()
    {
        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();

        $metafiles = $this->_mailMerge->getMetafiles(1,2);

        $this->assertTrue(is_array($metafiles));
        $this->assertTrue(2 === count($metafiles));

        foreach ($metafiles as $pageNumber => $pageContent) {
            $this->assertTrue(strlen($pageContent) > 5120);
        }
    }

    public function testGetAllMetafiles()
    {
        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->createDocument();

        $metafiles = $this->_mailMerge->getAllMetafiles();

        $this->assertTrue(is_array($metafiles));
        $this->assertTrue(2 === count($metafiles));

        foreach ($metafiles as $pageNumber => $pageContent) {
            $this->assertTrue(strlen($pageContent) > 5120);
        }
    }

    public function testGetFontNames()
    {
        $fonts = $this->_mailMerge->getFontNames();
        if (is_array($fonts) && count($fonts) > 5) {
            foreach (array('Courier New' , 'Verdana' , 'Arial' , 'Times New Roman') as $font) {
                if (in_array($font, $fonts)) {
                    $this->assertTrue(true);
                } else {
                    $this->assertTrue(false);
                }
            }
        } else {
            $this->assertTrue(false);
        }
    }

    public function testGetFieldNames()
    {
        $expectedResults = array(
            'phone', 'date', 'name', 'customer_number', 'invoice_number',
            'account_number', 'service_phone', 'service_fax', 'month',
            'monthly_fee', 'total_net', 'tax', 'tax_value', 'total');

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getFieldNames());
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getFieldNames());
    }

    public function testGetBlockFieldNames()
    {
        $expectedResults = array('connection_number', 'connection_duration', 'fee');

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getBlockFieldNames('connection'));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getBlockFieldNames('connection'));
    }

    public function testGetBlockNames()
    {
        $expectedResults = array('connection');

        // Remote template
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->_mailMerge->setRemoteTemplate(self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getBlockNames());
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);

        // Local template
        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, $this->_mailMerge->getBlockNames());
    }

    // -------------------------------------------------------------------------

    public function testUploadTemplate()
    {
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);
        $this->assertNull($this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);
    }

    public function testDownloadTemplate()
    {
        $expectedResults = '2f076af778ca5f8afc9661cfb9deb7c6';
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $template = $this->_mailMerge->downloadTemplate(self::TEST_TEMPLATE_2);
        $this->assertEquals($expectedResults, md5($template));
    }

    public function testDeleteTemplate()
    {
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);
        $templateDeleted = true;
        foreach($this->_mailMerge->listTemplates() as $template) {
            if($template['filename'] == self::TEST_TEMPLATE_2) {
                $templateDeleted = false;
            }
        }
        $this->assertTrue($templateDeleted);
    }

    public function testListTemplates()
    {
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);

        // Were templates uploaded and are being listed?
        $testTemplate1Exists = false;
        $testTemplate2Exists = false;

        $templates = $this->_mailMerge->listTemplates();
        foreach($templates as $template) {
            if(self::TEST_TEMPLATE_1 === $template['filename']) {
                $testTemplate1Exists = true;
            } elseif(self::TEST_TEMPLATE_2 === $template['filename']) {
                $testTemplate2Exists = true;
            }
        }
        $this->assertTrue($testTemplate1Exists && $testTemplate2Exists);

        // Is all info about templates available?
        $expectedResults = array('filename', 'fileSize', 'createTime', 'modifyTime');
        foreach($templates as $template) {
            $this->assertEquals($expectedResults, array_keys($template));
        }

        // Is all info about templates correct?
        foreach($templates as $template) {
            $this->assertTrue(strlen($template['filename']) > 0);
            $this->assertTrue($template['fileSize'] > 1);
            $this->assertTrue($template['createTime'] > mktime(0, 0, 0, 1, 1, 1980));
            $this->assertTrue($template['modifyTime'] > mktime(0, 0, 0, 1, 1, 1980));
        }

        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_1);
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);
    }

    public function testTemplateExists()
    {
        $this->_mailMerge->uploadTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_2);
        $this->assertTrue($this->_mailMerge->templateExists(self::TEST_TEMPLATE_2));
        $this->_mailMerge->deleteTemplate(self::TEST_TEMPLATE_2);
    }

    // -------------------------------------------------------------------------

    public function testUploadImage()
    {
        $this->_mailMerge->deleteImage(self::TEST_IMAGE_2);
        $this->assertNull($this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_2));
        $this->_mailMerge->deleteImage(self::TEST_IMAGE_2);
    }

    public function testDownloadImage()
    {
        $expectedResults = 'f8b663e465acd570414395d5c33541ab';
        $this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_2);
        $image = $this->_mailMerge->downloadImage(self::TEST_IMAGE_2);
        $this->assertEquals($expectedResults, md5($image));
    }

    public function testDeleteImage()
    {
        $this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_2);
        $this->_mailMerge->deleteImage(self::TEST_IMAGE_2);
        $imageDeleted = true;
        foreach($this->_mailMerge->listImages() as $image) {
            if($image['filename'] == self::TEST_IMAGE_2) {
                $imageDeleted = false;
            }
        }
        $this->assertTrue($imageDeleted);
    }

    public function testListImages()
    {
        $this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_1);
        $this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_2);

        // Where images uploaded and are being listed?
        $testImage1Exists = false;
        $testImage2Exists = false;

        $images = $this->_mailMerge->listImages();
        foreach($images as $image) {
            if(self::TEST_IMAGE_1 === $image['filename']) {
                $testImage1Exists = true;
            } elseif(self::TEST_IMAGE_2 === $image['filename']) {
                $testImage2Exists = true;
            }
        }
        $this->assertTrue($testImage1Exists && $testImage2Exists);

        // Is all info about images available?
        $expectedResults = array('filename', 'fileSize', 'createTime', 'modifyTime');
        foreach($images as $image) {
            $this->assertEquals($expectedResults, array_keys($image));
        }

        // Is all info about images correct?
        foreach($images as $image) {
            $this->assertTrue(strlen($image['filename']) > 0);
            $this->assertTrue($image['fileSize'] > 1);
            $this->assertTrue($image['createTime'] > mktime(0, 0, 0, 1, 1, 1980));
            $this->assertTrue($image['modifyTime'] > mktime(0, 0, 0, 1, 1, 1980));
        }

        $this->_mailMerge->deleteImage(self::TEST_IMAGE_1);
        $this->_mailMerge->deleteImage(self::TEST_IMAGE_2);
    }

    public function testImageExists()
    {
        $this->_mailMerge->uploadImage($this->_path . DIRECTORY_SEPARATOR . self::TEST_IMAGE_2);
        $this->assertTrue($this->_mailMerge->imageExists(self::TEST_IMAGE_2));
        $this->_mailMerge->deleteImage(self::TEST_IMAGE_2);
    }

    // -------------------------------------------------------------------------

    public function testAssocArrayToArrayOfArrayOfString()
    {
        $testValues = array(
            'a' => '1',
            'b' => '2',
            'c' => '3',
        );

        $expectedResults = array(
            array('a', 'b', 'c'),
            array('1', '2', '3'),
        );

        $actualResults = MailMerge::assocArrayToArrayOfArrayOfString($testValues);
        $this->assertEquals($expectedResults, $actualResults);
    }

    public function testMultiAssocArrayToArrayOfArrayOfString()
    {
        $testValues = array(
            array(
                'a' => '1',
                'b' => '2',
                'c' => '3',
            ),
            array(
                'a' => '4',
                'b' => '5',
                'c' => '6',
            ),
            array(
                'a' => '7',
                'b' => '8',
                'c' => '9',
            ),
        );

        $expectedResults = array(
            array('a', 'b', 'c'),
            array('1', '2', '3'),
            array('4', '5', '6'),
            array('7', '8', '9'),
        );
        $actualResults = MailMerge::multiAssocArrayToArrayOfArrayOfString($testValues);
        $this->assertEquals($expectedResults, $actualResults);
    }

    // -------------------------------------------------------------------------

    /* Tests for Premium LiveDocx service only */

    public function _setUpPremium()
    {
        if (!constant('TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_USERNAME') ||
            !constant('TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_PASSWORD') ||
            !constant('TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_WSDL')) {
            $this->markTestSkipped('LiveDocx tests disabled');
            return true;
        }

        $this->_mailMerge = new MailMerge();
        $this->_mailMerge->setUsername(TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_USERNAME)
                         ->setPassword(TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_PASSWORD)
                         ->setWsdl    (TESTS_ZEND_SERVICE_LIVEDOCX_PREMIUM_WSDL    );

        return true;
    }

    public function _tearDownPremium()
    {
        if (isset($this->_mailMerge)) {
            foreach ($this->_mailMerge->listTemplates() as $template) {
                $this->_mailMerge->deleteTemplate($template['filename']);
            }
            unset($this->_mailMerge);
        }

        return true;
    }

    public function testPremiumSetDocumentAccessPermissions()
    {
        $this->_setUpPremium();

        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->setDocumentAccessPermissions(
            array(
                'AllowHighLevelPrinting',
                'AllowExtractContents'
            ),
            'phpunit-2'
        );

        $this->assertNull($this->_mailMerge->createDocument());
        $this->assertTrue(strlen($this->_mailMerge->retrieveDocument('pdf')) > 100000);

        $this->_tearDownPremium();
    }

    public function testPremiumSetDocumentPassword()
    {
        $this->_setUpPremium();

        $testValues = array(
            'software' => 'phpunit',
            'licensee' => 'phpunit',
            'company'  => 'phpunit',
            'date'     => 'phpunit',
            'time'     => 'phpunit',
            'city'     => 'phpunit',
            'country'  => 'phpunit'
        );

        $this->_mailMerge->setLocalTemplate($this->_path . DIRECTORY_SEPARATOR . self::TEST_TEMPLATE_1);
        $this->_mailMerge->assign($testValues);
        $this->_mailMerge->setDocumentPassword('phpunit-1');

        $this->assertNull($this->_mailMerge->createDocument());
        $this->assertTrue(strlen($this->_mailMerge->retrieveDocument('pdf')) > 100000);

        $this->_tearDownPremium();
    }

    public function testPremiumGetDocumentAccessOptions()
    {
        $this->_setUpPremium();

        $expectedResults = array (
            'AllowAuthoring',
            'AllowAuthoringFields',
            'AllowContentAccessibility',
            'AllowDocumentAssembly',
            'AllowExtractContents',
            'AllowGeneralEditing',
            'AllowHighLevelPrinting',
            'AllowLowLevelPrinting',
            'AllowAll'
        );

        sort($expectedResults);

        $actualResults = $this->_mailMerge->GetDocumentAccessOptions();

        sort($actualResults);

        $this->assertEquals($expectedResults, $actualResults);

        $this->_tearDownPremium();
    }

    // -------------------------------------------------------------------------
    
}
