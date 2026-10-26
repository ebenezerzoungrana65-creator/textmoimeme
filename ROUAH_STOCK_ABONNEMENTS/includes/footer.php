</main>
</div>
<script>
document.querySelectorAll('[data-confirm]').forEach(function(formulaire) {
  formulaire.addEventListener('submit', function(e) {
    if (!confirm(formulaire.dataset.confirm)) e.preventDefault();
  });
});
</script>
</body>
</html>
