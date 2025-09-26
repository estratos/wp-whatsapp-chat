jQuery(document).ready(function($) {
    // Mostrar el botón después de cargar la página
    $(window).on('load', function() {
        $('#wpwhatsapp-chat').fadeIn(500);
    });
    
    // Efectos hover mejorados
    $('body').on('mouseenter', '.wpwhatsapp-button', function() {
        $(this).css('transform', 'translateY(-2px)');
    }).on('mouseleave', '.wpwhatsapp-button', function() {
        $(this).css('transform', 'translateY(0)');
    });
    
    // Tracking de clics (opcional para analytics)
    $('body').on('click', '.wpwhatsapp-button, .wpwhatsapp-shortcode-button', function() {
        if (typeof gtag !== 'undefined') {
            gtag('event', 'click', {
                'event_category': 'WhatsApp',
                'event_label': 'Chat Button Click'
            });
        }
    });
});
