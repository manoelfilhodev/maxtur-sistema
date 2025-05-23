<div id="loader" style="display:none; position:fixed; z-index:9999; background-color:rgba(255,255,255,0.8); width:100%; height:100%; top:0; left:0; justify-content:center; align-items:center;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
    </div>
</div>

<script>
    function showLoader() {
        document.getElementById('loader').style.display = 'flex';
    }

    function hideLoader() {
        document.getElementById('loader').style.display = 'none';
    }
</script>
