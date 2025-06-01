{*
 * RetJet - PrestaShop Integration Module
 *
 * @author    RetJet
 * @copyright Copyright (c) RetJet
 *
 * https://www.retjet.com
 *}

<script>
window.retJet = {
    "companyId": "{$companyId}",
    "lang": "{$lang|escape:'html':'UTF-8'}"
};
(function(){
    var d=document, s=d.createElement("script");
    s.src="https://app.retjet.com/jsplugin.js";
    s.async=1;
    d.getElementsByTagName("head")[0].appendChild(s);
})();
</script>