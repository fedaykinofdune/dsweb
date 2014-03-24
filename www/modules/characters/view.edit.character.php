<style>
.edit-label { color: #888; font-size: 11px; display: block; margin: 0 0 8px 0; }
</style>
<div class="page">
    <h3><span style="color:#888;">Character &raquo;</span> (<?=$Character->getID();?>) <?=$Character->getName();?></h3>
    
    <h5>Account</h5>
    <table class="generic-table" border="0" cellpadding="10" cellspacing="0">
        <tr>
            <td><span class="edit-label">ID</span> <?=$Character->Account->getID();?></td>
            <td><span class="edit-label">Name</span> <?=$Character->Account->getName();?></td>
            <td><span class="edit-label">Email #1</span> <?=$Character->Account->getEmail1();?></td>
            <td><span class="edit-label">Email #2</span> <?=$Character->Account->getEmail2();?></td>
            <td><span class="edit-label">Created</span> <?=$Character->Account->getCreated();?></td>
            <td><span class="edit-label">Modified</span> <?=$Character->Account->getModified();?></td>
            <td><span class="edit-label">Status</span> <?=$Character->Account->getStatus();?></td>
            <td><span class="edit-label">Privledges</span> <?=$Character->Account->getPrivledges();?></td>
        </tr>
    </table>

    <h5>Character</h5>
    <table class="generic-table" border="0" cellpadding="10" cellspacing="0">
        <tr>
            <td><span class="edit-label">ID</span> <?=$Character->getID();?></td>
            <td><span class="edit-label">Name</span> <?=$Character->getName();?></td>
            <td><span class="edit-label">Rotation</span> <?=$Character->getRotation();?></td>
            <td><span class="edit-label">Position</span> <?=json_encode($Character->getPosition());?></td>
            <td><span class="edit-label">Boundary</span> <?=$Character->getBoundary();?></td>
            <td><span class="edit-label">Playtime</span> <?=$Character->getPlaytime();?></td>
            <td><span class="edit-label">GMLevel</span> <?=$Character->getGMLevel();?></td>
        </tr>
    </table>

</div>