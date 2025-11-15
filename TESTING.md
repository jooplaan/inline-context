# Inline Context - Manual Testing Script

## Test Environment Setup

- [ ] WordPress installation with block editor enabled
- [ ] Inline Context plugin activated
- [ ] At least 3 test posts/pages created (Post A, Post B, Post C)
- [ ] Admin settings configured (check display mode: inline or tooltip)

---

## Test Suite 1: Reusable Notes Creation & Usage

### 1.1 Create Reusable Note

1. **Open Post A in block editor**
2. **Select text** "important term" (or any text)
3. **Click inline context toolbar button** (icon in rich text toolbar)
4. **In Create tab:**
   - Enter content: "This is a test note"
   - **Check** "Use as reusable note" checkbox
   - Click "Save"
5. **Verify:**
   - [ ] Text is now highlighted/linked
   - [ ] Link has anchor icon or distinctive styling

### 1.2 Reuse Note in Second Post

1. **Open Post B in block editor**
2. **Select different text** "same concept"
3. **Click inline context toolbar button**
4. **Switch to "Search" tab**
5. **Type** "test" in search box
6. **Verify:**
   - [ ] Search results show the note created in 1.1
   - [ ] Note title and excerpt are visible
7. **Click on search result** to select it
8. **Click "Save"**
9. **Verify:**
   - [ ] Text is linked with inline context
   - [ ] Same note ID is being used (`data-note-id` attribute should match)

**Expected:** Two different posts now reference the same reusable note.

---

## Test Suite 2: Content Synchronization

### 2.1 Edit Reusable Note Content

1. **Go to** Posts → Inline Context Notes
2. **Find** "Test Note" in list
3. **Verify list view columns:**
   - [ ] "Reusable" column shows "Yes"
   - [ ] "Usage Count" shows "2"
   - [ ] "Used In" shows links to Post A and Post B
4. **Click "Edit"** on the note
5. **Change content** to: "This is UPDATED content"
6. **Click "Update"**

### 2.2 Verify Synchronization

1. **View Post A on frontend**
2. **Click the inline context link**
3. **Verify:**
   - [ ] Content shows "This is UPDATED content"
   - [ ] Content displays correctly (inline or tooltip based on settings)
4. **View Post B on frontend**
5. **Click the inline context link**
6. **Verify:**
   - [ ] Content also shows "This is UPDATED content"
   - [ ] Both posts display the synchronized updated content

**Expected:** Editing a reusable note updates content in ALL posts using it.

---

## Test Suite 3: Deletion Protection & Cleanup

### 3.1 Attempt to Delete Used Reusable Note

1. **Go to** Posts → Inline Context Notes
2. **Hover over** "Test Note" (which is used in 2 posts)
3. **Click "Trash"**
4. **Verify:**
   - [ ] Confirmation dialog appears
   - [ ] Message shows: "This reusable note is used X times in Y posts"
   - [ ] Lists affected posts (Post A, Post B)
5. **Click "Cancel"** first
   - [ ] Note remains in list, not deleted
6. **Click "Trash"** again
7. **This time click "OK"** to confirm
8. **Verify:**
   - [ ] Note moves to trash
   - [ ] All inline context links removed from Post A and Post B
   - [ ] Link text remains but `<a>` tags are stripped

### 3.2 Bulk Delete Reusable Notes

1. **Create a new reusable note** "Bulk Test" and use it in Post A
2. **Go to** Inline Context Notes list
3. **Select checkbox** for "Bulk Test" note
4. **Select "Move to Trash"** from bulk actions
5. **Click "Apply"**
6. **Verify:**
   - [ ] Confirmation shows usage count and affected posts
   - [ ] After confirmation, note is deleted from all posts
   - [ ] Text content remains, only `<a>` tags removed

---

## Test Suite 4: Non-Reusable Notes

### 4.1 Create Non-Reusable Note

1. **Open Post C in block editor**
2. **Select text** "unique note"
3. **Click inline context toolbar button**
4. **In Create tab:**
   - Enter content: "This is unique to Post C"
   - **Uncheck** "Use as reusable note" checkbox
   - Click "Save"

### 4.2 Verify Non-Reusable Behavior

1. **Go to** Posts → Inline Context Notes
2. **Find the new note** in list
3. **Verify:**
   - [ ] "Reusable" column shows "No"
   - [ ] "Usage Count" shows "1"
   - [ ] "Used In" shows only Post C

### 4.3 Delete Non-Reusable Note

1. **Trash the note** from Inline Context Notes list
2. **Verify:**
   - [ ] No special confirmation (or minimal warning)
   - [ ] Note is deleted
3. **Edit Post C** in block editor
4. **Verify:**
   - [ ] Inline context link is removed
   - [ ] Text content remains

**Expected:** Non-reusable notes can be deleted without affecting other posts.

---

## Test Suite 5: REST API & Search

### 5.1 Search Functionality

1. **Open any post in block editor**
2. **Select text** and open inline context toolbar
3. **Switch to "Search" tab**
4. **Test various searches:**
   - Search "test" → Should find matching notes
   - Search "updated" → Should find notes with that word
   - Search gibberish "xyzabc" → Should show "No notes found"
5. **Verify:**
   - [ ] Results appear instantly (no page reload)
   - [ ] Each result shows title and excerpt
   - [ ] Clicking result loads its content

### 5.2 Filter Reusable Only

1. **In Search tab**, look for filter option (if available)
2. **Enable "Reusable only"** filter
3. **Verify:**
   - [ ] Only notes marked as reusable appear
   - [ ] Non-reusable notes are excluded from results

---

## Test Suite 6: Frontend Display

### 6.1 Test Inline Display Mode

1. **Go to** Settings → Inline Context
2. **Select "Inline" display mode**
3. **Save settings**
4. **View a post with inline context** on frontend
5. **Click the inline context link**
6. **Verify:**
   - [ ] Note content expands directly below the link
   - [ ] Content slides down smoothly (animation)
   - [ ] Left accent bar is visible
   - [ ] Clicking again collapses the content
7. **Test keyboard navigation:**
   - Tab to link, press Space/Enter
   - [ ] Note expands on keyboard activation
   - [ ] Focus moves to note content
   - [ ] Can tab through links inside note

### 6.2 Test Tooltip Display Mode

1. **Go to** Settings → Inline Context
2. **Select "Tooltip" display mode**
3. **Save settings**
4. **View a post with inline context** on frontend
5. **Click the inline context link**
6. **Verify:**
   - [ ] Note appears as floating tooltip
   - [ ] Tooltip positioned above or below link (smart positioning)
   - [ ] Tooltip has close button (×) in top-right
   - [ ] Arrow pointer indicates which link triggered it
   - [ ] Clicking outside or on close button dismisses tooltip
   - [ ] Pressing Escape closes tooltip and returns focus
7. **Test viewport boundaries:**
   - Click link near top of page → Tooltip appears below
   - Click link near bottom → Tooltip flips above
   - [ ] Tooltip never goes off-screen

### 6.3 Test Direct Anchor Linking

1. **Click "Copy link to this note"** in editor
2. **Paste link** in browser address bar
3. **Verify:**
   - [ ] Page loads and scrolls to the note
   - [ ] Note automatically opens
   - [ ] Note is highlighted or focused

### 6.4 Test Multiple Notes

1. **Create a post with 3+ inline context notes**
2. **View on frontend**
3. **Click multiple notes** (don't close between clicks)
4. **Verify:**
   - [ ] Multiple notes can be open simultaneously
   - [ ] Each displays correctly
   - [ ] No interference between notes

---

## Test Suite 7: Categories

### 7.1 Create Category

1. **Go to** Posts → Categories → Inline Context Categories
2. **Create new category** "Technical Terms"
3. **Set icon** (if available) and color
4. **Save**

### 7.2 Assign Category to Note

1. **Open post in block editor**
2. **Create new inline context note**
3. **Select category** "Technical Terms" from dropdown
4. **Save**
5. **Verify:**
   - [ ] Category is stored with note
   - [ ] Visual indicator (color/icon) appears if styled

---

## Test Suite 8: Rich Text Content

### 8.1 Test Rich Text Editor

1. **Create new inline context note**
2. **In QuillEditor, add:**
   - **Bold text** (Ctrl/Cmd+B)
   - **Italic text** (Ctrl/Cmd+I)
   - **Bulleted list**
   - **Numbered list**
   - **Link** using toolbar
3. **Save and view on frontend**
4. **Verify:**
   - [ ] All formatting preserved
   - [ ] Links work correctly
   - [ ] Lists display properly
   - [ ] No XSS vulnerabilities (content sanitized)

### 8.2 Test Internal Links

1. **In note editor, click "Add Link"**
2. **Search for internal post/page**
3. **Select from results**
4. **Save**
5. **View on frontend**
6. **Verify:**
   - [ ] Internal link opens in same tab
   - [ ] Link works correctly

### 8.3 Test External Links

1. **Add external URL** (e.g., <https://example.com>)
2. **Save and view on frontend**
3. **Verify:**
   - [ ] External link has `rel="noopener noreferrer"`
   - [ ] Link opens in new tab (if configured)

---

## Test Suite 9: v2.2 - Convert Reusable to Non-Reusable

### 9.1 Create Reusable Note for Testing

1. **Open Post A** in block editor
2. **Create reusable note** "Convertible Note" with content "Original reusable"
3. **Use same note in Post B** (via Search tab)
4. **Verify:**
   - [ ] Both posts use same `data-note-id`
   - [ ] Note shows in CPT list with "Reusable: Yes" and "Usage Count: 2"

### 9.2 Convert to Non-Reusable in One Post

1. **In Post A, click on the inline context link** to edit it
2. **Verify initial state:**
   - [ ] "Use as reusable note" checkbox is **checked**
   - [ ] Checkbox is **enabled** (not disabled)
3. **Uncheck** "Use as reusable note" checkbox
4. **Verify:**
   - [ ] Confirmation modal appears
   - [ ] Title: "Make note non-reusable?"
   - [ ] Message: "Are you sure you want to mark this note as no longer reusable? A new note will be created."
   - [ ] Two buttons: "Cancel" and "Create New Note"

### 9.3 Test Cancel Action

1. **Click "Cancel"** button
2. **Verify:**
   - [ ] Modal closes
   - [ ] Checkbox remains checked
   - [ ] No changes made to note
   - [ ] Still references original note ID

### 9.4 Test Confirm Action

1. **Uncheck checkbox again**
2. **This time click "Create New Note"**
3. **Click "Save"** to save the post
4. **Verify in editor:**
   - [ ] Note is saved
   - [ ] Different `data-note-id` than original
5. **Go to Inline Context Notes list**
6. **Verify:**
   - [ ] Original "Convertible Note" still exists
   - [ ] Original note shows "Usage Count: 1" (only Post B)
   - [ ] New note appears with "Reusable: No"
   - [ ] New note shows "Usage Count: 1" (only Post A)
7. **View Post A on frontend:**
   - [ ] Content still displays correctly
   - [ ] Uses new note ID
8. **View Post B on frontend:**
   - [ ] Still uses original reusable note
   - [ ] Content unchanged

### 9.5 Edit Converted Note

1. **In Post A editor**, edit the inline context
2. **Change content** to "Now unique to Post A"
3. **Verify checkbox state:**
   - [ ] "Use as reusable note" is unchecked
   - [ ] Can be checked again to make it reusable
4. **Save**
5. **View Post A:** Should show "Now unique to Post A"
6. **View Post B:** Should still show original content
7. **Verify:**
   - [ ] Changes in Post A don't affect Post B
   - [ ] Notes are now independent

**Expected:** Converting reusable → non-reusable creates a new independent note without affecting the original.

---

## Test Suite 10: Edge Cases

### 10.1 Empty Content

1. **Try to save note with empty content**
2. **Verify:** [ ] Appropriate validation/error message

### 10.2 Very Long Content

1. **Create note with 2000+ words**
2. **Verify:**
   - [ ] Saves correctly
   - [ ] Displays without layout issues
   - [ ] Scrollable if needed in tooltip mode

### 10.3 Special Characters

1. **Create note with:** `<script>alert('test')</script>`
2. **Verify:**
   - [ ] Content is sanitized (script doesn't execute)
   - [ ] Displays as text, not as code

### 10.4 Multiple Categories

1. **Test behavior when category is deleted**
2. **Verify:** [ ] Notes with deleted category handle gracefully

---

## Test Suite 11: Uninstall (DESTRUCTIVE - Run Last)

### 11.1 Standard Uninstall

1. **Go to** Plugins → Installed Plugins
2. **Deactivate** Inline Context
3. **Click "Delete"**
4. **If prompted, choose:** "Remove inline context links from content"
5. **Verify:**
   - [ ] All CPT posts deleted
   - [ ] All categories removed
   - [ ] Inline context links stripped from posts (text remains)
   - [ ] No orphaned data in database

---

## Testing Checklist Summary

**Core Features:**

- [x] Reusable notes creation
- [x] Content synchronization
- [x] Deletion protection with cleanup
- [x] Non-reusable notes
- [x] Search functionality
- [x] REST API endpoints

**Display Modes:**

- [x] Inline display
- [x] Tooltip display
- [x] Anchor linking
- [x] Keyboard navigation

**v2.2 Features:**

- [x] Convert reusable to non-reusable
- [x] Confirmation modal
- [x] New note creation on conversion
- [x] Independent note editing after conversion

**Quality:**

- [x] Rich text formatting
- [x] XSS prevention
- [x] Accessibility (ARIA, keyboard)
- [x] Edge cases handled

---

## Bug Report Template

If issues are found during testing:

```text
**Test:** [Test Suite and Number]
**Expected:** [What should happen]
**Actual:** [What actually happened]
**Steps to Reproduce:**
1.
2.
3.

**Browser:** [Chrome/Firefox/Safari/Edge]
**WordPress Version:**
**Plugin Version:**
**Console Errors:** [If any]
```

---

## Performance Notes

**Monitor during testing:**

- [ ] Page load times with many inline contexts
- [ ] Search response time
- [ ] Editor performance with QuillEditor
- [ ] Frontend animation smoothness
- [ ] No console errors or warnings
