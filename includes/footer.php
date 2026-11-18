</main>
<footer class="footer">
 <div class="container footer-grid">
  <div>
   <strong><?= e(SITE_NAME) ?></strong>
   <p><?= e(setting('about', 'Mobilisation, suivi et gestion des financements extérieurs.')) ?></p>
   <p style="margin-top:10px"><?= e(setting('address')) ?><?php if (setting('phone')): ?><br>Tél. : <?= e(setting('phone')) ?><?php endif; ?><?php if (setting('email')): ?><br><?= e(setting('email')) ?><?php endif; ?></p>
  </div>
  <div>
   <strong>Navigation</strong>
   <ul>
    <li><a href="<?= e(url('direction.php')) ?>">Le Directeur</a></li>
    <li><a href="<?= e(url('organigramme.php')) ?>">Organigramme</a></li>
    <li><a href="<?= e(url('publications.php')) ?>">Publications</a></li>
    <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
    <li><a href="<?= e(url('')) ?>">#Djibouti #FinancesPubliques #Economie #Développement #BonneGouvernance #Transparence #CoopérationInternationale #RépubliqueDeDjibouti</a></li>
   </ul>
  </div>
  <div>
   <strong>Administration</strong>
   <ul>
    <li><a href="<?= e(url('admin/login.php')) ?>">Connexion administrateur</a></li>
   </ul>
  </div>
 </div>
 <div class="container copyright">© <?= date('Y') ?> <?= e(SITE_NAME) ?> — République de Djibouti</div>
</footer>
</body>
</html>
