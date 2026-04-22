<?php
// ──────────────────────────────────────────────────────────────────────────────
// ViewTest — Unit tests for the View and Loader classes
// Author: dblack
// Email: dblack@db3.net
// Copyright (c) 2026 db3.net. All rights reserved.
// ──────────────────────────────────────────────────────────────────────────────

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testRenderReturnsHtmlAsVar(): void
    {
        $html = View::render('welcome', [
            'title'   => 'Test Title',
            'message' => 'Test Message',
        ], true);

        $this->assertStringContainsString('Test Title', $html);
        $this->assertStringContainsString('Test Message', $html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
    }

    public function testRenderEchoes(): void
    {
        ob_start();
        View::render('welcome', [
            'title'   => 'Echo Test',
            'message' => 'Echo Message',
        ]);
        $output = ob_get_clean();

        $this->assertStringContainsString('Echo Test', $output);
    }

    public function testRenderBlocksPathTraversal(): void
    {
        ob_start();
        View::render('../../etc/passwd', []);
        $output = ob_get_clean();

        $this->assertStringContainsString('View not found', $output);
    }

    public function testRenderBlocksDotDotInViewName(): void
    {
        ob_start();
        View::render('../config/config', []);
        $output = ob_get_clean();

        $this->assertStringContainsString('View not found', $output);
    }

    public function testRenderHandlesMissingView(): void
    {
        ob_start();
        View::render('this_view_does_not_exist', []);
        $output = ob_get_clean();

        $this->assertStringContainsString('View not found', $output);
    }

    public function test404ViewRenders(): void
    {
        $html = View::render('404', [
            'title'   => '404 — Not Found',
            'message' => 'Page not found.',
        ], true);

        $this->assertStringContainsString('404', $html);
        $this->assertStringContainsString('Page not found.', $html);
    }
}
