$(document).ready(function() {
    /* Méta-titre et méta-description automatiques */
    $('#typecategorie_nom').on('keyup', function(){
        if($('body').hasClass('new')){
            titre = $(this).val();
            $('#typecategorie_SEO_metaTitre').val(titre);
            apercuGoogle($('#typecategorie_SEO_metaTitre'));
            $('#typecategorie_SEO_metaDescription').val(titre);
            apercuGoogle($('#typecategorie_SEO_metaDescription'));
        }
    });
});