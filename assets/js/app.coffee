jQuery ($) ->
  class EDDPreview 
    constructor: ->
      @frame = false
      @action = '.edd-add-preview'

      @createFrame();
      @bindActions();
      @toggleActions();

    bindActions: =>
      $( '#edd_variable_price_fields' ).on 'click', @action, (e) =>
        e.preventDefault()
        
        @bindFrame e.target

      $( '#edd_price_fields' ).on 'change', '.edd_repeatable_default_input', (e) =>
        console.log 'clicked'
        @toggleActions()

      $( 'body' ).on 'click', '.submit .edd_add_repeatable', (e) =>
        @toggleActions()

    toggleActions: =>
      rows = $( '.edd_variable_prices_wrapper' )

      console.log rows

      rows.each (i, el) =>
        radio = $( el ).find '.edd_repeatable_default_input'

        $( el ).find( @action ).toggle( ! radio.is( ':checked' ) )

    bindFrame: (el) =>
      @frame.open()

      @frame.on 'select', =>
        @attachFile el 

    createFrame: =>
      @frame = wp.media.frames._playlist = wp.media
        title: EDDPlaylists.i18n.title
        button: 
          text: EDDPlaylists.i18n.button
        multiple: false

    attachFile: (el) =>
      attachment = @frame.state().get( 'selection' ).first().toJSON()

      $( el ).parent().next().val attachment.id

  new EDDPreview()
