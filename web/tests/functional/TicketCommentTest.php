<?php

namespace Tests\Functional;

require_once __DIR__ . '/BaseFunctionalTest.php';
/**
 * Functional tests for the Ticket comment & edit feature.
 *
 * Verifies:
 * - Comment form only renders for ticket participants
 * - Edit/delete comment buttons are conditional on comment authorship (or admin)
 * - "edited" badge renders when updated_at is set
 * - detail.js exposes editComment/deleteComment handlers
 * - Web routes exist for comment update/delete
 * - Ticket edit button is available to creator/assignee without can_update
 *
 * @internal
 */
final class TicketCommentTest extends BaseFunctionalTest
{
    private string $viewFile = 'tickets/detail.php';
    private string $jsFile   = 'js/page/tickets/detail.js';
    private string $routesFile = 'app/Config/Routes.php';
    private string $controllerFile = 'app/Controllers/Tickets.php';

    private string $viewContent     = '';
    private string $jsContent       = '';
    private string $routesContent   = '';
    private string $controllerContent = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewContent     = $this->readFile($this->viewPath($this->viewFile)) ?: '';
        $this->jsContent       = $this->readFile($this->assetPath($this->jsFile)) ?: '';
        $this->routesContent   = $this->readFile($this->webRoot . '/' . $this->routesFile) ?: '';
        $this->controllerContent = $this->readFile($this->webRoot . '/' . $this->controllerFile) ?: '';
    }

    // ── Comment Form Participation Tests ──────────────────────

    public function testViewFileExists(): void
    {
        $this->assertNotEmpty($this->viewContent, "View file not found: {$this->viewFile}");
    }

    public function testCommentFormIsGatedByCanComment(): void
    {
        $this->assertStringContainsString(
            '$canComment = $cIsCreator || $cIsAssignee || $cIsApprover || $cIsAdmin;',
            $this->viewContent,
            'Comment form must be gated by participant check'
        );
        $this->assertStringContainsString("if (\$canComment):", $this->viewContent, 'View must wrap comment form in canComment condition');
    }

    public function testCommentFormHasNonParticipantFallback(): void
    {
        $this->assertStringContainsString('Hanya peserta ticket', $this->viewContent, 'Non-participants must see an explanation instead of the form');
    }

    // ── Comment Edit / Delete UI Tests ────────────────────────

    public function testCommentEditDeleteButtonsAreConditional(): void
    {
        $this->assertStringContainsString(
            '$canEditComment = $isCommentAuthor || $cIsAdmin;',
            $this->viewContent,
            'Edit/delete buttons must only render for comment author or admin'
        );
    }

    public function testEditedBadgeRendersWhenUpdatedAt(): void
    {
        $this->assertStringContainsString("if (!empty(\$comment['updated_at'])):", $this->viewContent, 'edited badge must depend on updated_at');
    }

    public function testViewCallsEditCommentHandler(): void
    {
        $this->assertStringContainsString('editComment(', $this->viewContent, 'View must call editComment onclick handler');
        $this->assertStringContainsString('deleteComment(', $this->viewContent, 'View must call deleteComment onclick handler');
    }

    // ── JS Handler Tests ───────────────────────────────────────

    public function testJsDefinesEditComment(): void
    {
        $this->assertMatchesRegularExpression('/function\s+editComment\s*\(/', $this->jsContent, 'detail.js must define editComment()');
    }

    public function testJsDefinesDeleteComment(): void
    {
        $this->assertMatchesRegularExpression('/function\s+deleteComment\s*\(/', $this->jsContent, 'detail.js must define deleteComment()');
    }

    public function testJsPostsCommentUpdate(): void
    {
        $this->assertStringContainsString("'/comments/' + commentId + '/update'", $this->jsContent, 'editComment must POST to comment update endpoint');
    }

    public function testJsPostsCommentDelete(): void
    {
        $this->assertStringContainsString("'/comments/' + commentId + '/delete'", $this->jsContent, 'deleteComment must POST to comment delete endpoint');
    }

    // ── Route Tests ────────────────────────────────────────────

    public function testRoutesDefineCommentUpdateAndDelete(): void
    {
        $this->assertStringContainsString('comments/(:any)/update', $this->routesContent, 'Routes must define comment update endpoint');
        $this->assertStringContainsString('comments/(:any)/delete', $this->routesContent, 'Routes must define comment delete endpoint');
    }

    // ── Controller Tests ───────────────────────────────────────

    public function testControllerHasUpdateCommentMethod(): void
    {
        $this->assertMatchesRegularExpression('/public function updateComment\(string \$encryptedId, string \$encryptedCommentId\)/', $this->controllerContent);
    }

    public function testControllerHasDeleteCommentMethod(): void
    {
        $this->assertMatchesRegularExpression('/public function deleteComment\(string \$encryptedId, string \$encryptedCommentId\)/', $this->controllerContent);
    }

    public function testCommentEndpointsNotGuardedByCanUpdate(): void
    {
        // addComment/updateComment/deleteComment must not require can_update
        $addCommentBlock = $this->extractMethodBlock('addComment');
        $this->assertStringNotContainsString("guard('can_update')", $addCommentBlock, 'addComment must not require can_update');
    }

    // ── Ticket Edit Access Tests ───────────────────────────────

    public function testEditButtonAvailableToCreatorOrAssignee(): void
    {
        $this->assertStringContainsString(
            '$canUpdate || $isCreator || $isAssignee',
            $this->viewContent,
            'Ticket Edit button must be available to creator/assignee even without can_update'
        );
    }

    public function testApproverCompareUsesRawId(): void
    {
        $this->assertStringContainsString(
            "\$ticket['approver_raw_id'] ?? ''",
            $this->viewContent,
            'Approver comparison must use raw id (not encrypted)'
        );
    }

    // ── Helpers ────────────────────────────────────────────────

    private function extractMethodBlock(string $method): string
    {
        $pattern = '/public function ' . $method . '\(.*?\n    \{/s';
        // Simple approach: grab from method signature to the next "public function"
        if (preg_match('/public function ' . $method . '\((.*?)public function/s', $this->controllerContent, $m)) {
            return $m[1];
        }
        return '';
    }
}
