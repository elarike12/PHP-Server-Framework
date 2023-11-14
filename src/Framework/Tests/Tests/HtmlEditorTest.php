<?php

namespace Framework\Tests\Tests;

use PHPUnit\Framework\TestCase;
use Framework\Utils\HtmlEditor;

class HtmlEditorTest extends TestCase {
    private HtmlEditor $htmlEditor;

    public function setUp(): void {
        $html = '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Framework</title>
    </head>
    <body>
        <div>
            <div>Test 1</div>
            <div>Test 2</div>
            <div>Test 3</div>
            Test 4
        </div>
    </body>
</html>';
        $this->htmlEditor = new HtmlEditor($html);
    }

    public function testSearch() {
        $search = $this->htmlEditor->search('//body/div/div[1]')[0] ?? null;
        $this->assertInstanceOf(HtmlEditor::class, $search);
        $this->assertSame('<div>Test 1</div>', trim($search->getHtmlContent()));
        $search = $this->htmlEditor->search('//body/div/div[2]')[0] ?? null;
        $this->assertInstanceOf(HtmlEditor::class, $search);
        $this->assertSame('<div>Test 2</div>', trim($search->getHtmlContent()));
        $search = $this->htmlEditor->search('//body/div/text()[4]')[0] ?? null;
        $this->assertInstanceOf(HtmlEditor::class, $search);
        $this->assertSame('Test 4', trim($search->getHtmlContent()));
    }

    public function testSearchNoResults() {
        $search = $this->htmlEditor->search('//body/p');
        $this->assertEmpty($search);
    }

    public function testGetChildren() {
        $search = $this->htmlEditor->search('//body/div')[0]->getChildren();
        $this->assertCount(4, $search);
    }

    public function testGetNthChildren() {
        $search = $this->htmlEditor->search('//body/div')[0];
        $this->assertSame('<div>Test 1</div>', trim($search->getChildren(nth: 1)[0]->getHtmlContent()));
        $this->assertSame('<div>Test 2</div>', trim($search->getChildren(nth: 2)[0]->getHtmlContent()));
        $this->assertSame('<div>Test 3</div>', trim($search->getChildren(nth: 3)[0]->getHtmlContent()));
        $this->assertSame('Test 4', trim($search->getChildren(nth: 4)[0]->getHtmlContent()));
    }

    public function testGetParent() {
        $search = $this->htmlEditor->search('//body/div/div[1]')[0];
        $search = $search->getParent()->getChildren();
        $this->assertCount(4, $search);
    }

    public function testSearchCloneResults() {
        $search = $this->htmlEditor->search('//body/div/div[1]', true)[0];
        $this->assertNull($search->getParent());
    }

    public function testAppend() {
        $this->htmlEditor->append('<div>Test 5</div>', '//body/div[1]');
        $this->assertSame('<div>Test 5</div>', trim($this->htmlEditor->getChildren('//body')[1]->getHtmlContent()));
        $this->htmlEditor->append('<div>Test 6</div>', '//body/div[1]');
        $this->assertSame('<div>Test 6</div>', trim($this->htmlEditor->getChildren('//body')[1]->getHtmlContent()));
        $this->assertSame('<div>Test 5</div>', trim($this->htmlEditor->getChildren('//body')[2]->getHtmlContent()));
    }

    public function testInnerHtml() {
        $this->htmlEditor->append('<div>Test 5</div>', '//body/div', true);
        $this->assertSame('<div>Test 5</div>', trim($this->htmlEditor->getChildren('//body/div')[4]->getHtmlContent()));
        $this->htmlEditor->append('<div>Test 6</div>', '//body/div', true);
        $this->assertSame('<div>Test 6</div>', trim($this->htmlEditor->getChildren('//body/div')[5]->getHtmlContent()));
        $this->assertSame('<div>Test 5</div>', trim($this->htmlEditor->getChildren('//body/div')[4]->getHtmlContent()));
    }

    public function testPrepend() {
        $this->htmlEditor->prepend('<div>Test 1</div>', '//body', true);
        $this->assertSame('<div>Test 1</div>', trim($this->htmlEditor->getChildren('//body')[0]->getHtmlContent()));
        $this->htmlEditor->prepend('<div>Test 2</div>', '//body', true);
        $this->assertSame('<div>Test 2</div>', trim($this->htmlEditor->getChildren('//body')[0]->getHtmlContent()));
        $this->assertSame('<div>Test 1</div>', trim($this->htmlEditor->getChildren('//body')[1]->getHtmlContent()));
        $this->htmlEditor->prepend('<div>Test 3</div>', '//body', false);
        $this->assertSame('<div>Test 3</div>', trim($this->htmlEditor->getPreviousSiblings('//body')[0]->getHtmlContent()));
        $this->htmlEditor->prepend('<div>Test 4</div>', '//body', false);
        $this->assertSame('<div>Test 4</div>', trim($this->htmlEditor->getPreviousSiblings('//body')[0]->getHtmlContent()));
        $this->assertSame('<div>Test 3</div>', trim($this->htmlEditor->getPreviousSiblings('//body')[1]->getHtmlContent()));
    }

    public function testReplace() {
        $this->htmlEditor->getChildren('//body/div')[0]->replace('<p>Test 1</p>');
        $this->assertSame('<p>Test 1</p>', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));
        $this->htmlEditor->getChildren('//body/div')[1]->replace('<p>Test 2</p>');
        $this->assertSame('<p>Test 2</p>', trim($this->htmlEditor->getChildren('//body/div')[1]->getHtmlContent()));
    }

    public function testReplaceInnerHtml() {
        $this->htmlEditor->getChildren('//body/div')[0]->replace('<p>Test 1</p>', innerHtml: true);
        $this->assertSame('<div><p>Test 1</p></div>', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));
        $this->htmlEditor->getChildren('//body/div')[1]->replace('<p>Test 2</p>', innerHtml: true);
        $this->assertSame('<div><p>Test 2</p></div>', trim($this->htmlEditor->getChildren('//body/div')[1]->getHtmlContent()));
    }

    public function testRemove() {
        $this->htmlEditor->getChildren('//body/div')[0]->remove();
        $this->assertSame('<div>Test 2</div>', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));
        $this->htmlEditor->getChildren('//body/div')[0]->remove();
        $this->assertSame('<div>Test 3</div>', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));
        $this->htmlEditor->getChildren('//body/div')[0]->remove();
        $this->assertSame('Test 4', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));

    }

    public function testAttributes() {
        $this->htmlEditor->getChildren('//body/div')[0]->addAttributes(['test1' => 'test 1']);
        $this->htmlEditor->getChildren('//body/div')[1]->addAttributes(['test2' => 'test 2']);
        $this->htmlEditor->getChildren('//body/div')[2]->addAttributes(['test3' => 'test 3']);

        $this->assertSame('<div test1="test 1">Test 1</div>', trim($this->htmlEditor->getChildren('//body/div')[0]->getHtmlContent()));
        $this->assertSame('<div test2="test 2">Test 2</div>', trim($this->htmlEditor->getChildren('//body/div')[1]->getHtmlContent()));
        $this->assertSame('<div test3="test 3">Test 3</div>', trim($this->htmlEditor->getChildren('//body/div')[2]->getHtmlContent()));
    }
}
