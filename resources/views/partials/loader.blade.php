<style type="text/css">
    .loading-overlay {
        display: none;
        background: rgba(255, 255, 255, 0.7);
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: 0;
        z-index: 9998;
        align-items: center;
        justify-content: center;
    }

    .loading-overlay.is-active {
        display: flex;
    }
</style>
<div class="loading-overlay">
    <span class="fa-spin spinner-border"></span>
</div>
<script type="text/javascript">
    let overlay = document.querySelector('.loading-overlay');
    function toggleLoader(text=null) {
        const loadingText = overlay.querySelector('.loadingText');
        loadingText && loadingText.remove();
        overlay && overlay.classList.toggle('is-active');
        if(text){
            let textEle = document.createElement('span');
            textEle.textContent = text;
            overlay.appendChild(textEle);
        }
    }
</script>