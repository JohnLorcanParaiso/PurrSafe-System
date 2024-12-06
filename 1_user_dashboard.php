// ... existing code ...
<td>
    <?= htmlspecialchars($row['cat_name']) ?>
    <?php if (!empty($row['edited_at'])): ?>
        <span class="badge text-white" style="background-color: #6f42c1; font-size: 0.65rem;">Edited</span>
    <?php endif; ?>
</td>
// ... existing code ...