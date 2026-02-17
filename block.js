(function(blocks, element){
    var el = element.createElement;

    blocks.registerBlockType('wc/loop-variations', {
        edit: function(){
            return el(
                'div',
                { style: { padding: '8px', border: '1px dashed #ccc' } },
                'Woo Variations will render on frontend'
            );
        },
        save: function(){
            return null;
        }
    });

})(window.wp.blocks, window.wp.element);
