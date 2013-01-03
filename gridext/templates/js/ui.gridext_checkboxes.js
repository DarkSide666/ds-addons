$.widget("ui.gridext_checkboxes", {
    
    c: undefined, // container (tbody.ui-selectable)
    o: undefined, // selectable objects (tr.ui-selectee)
    dst: undefined, // destination field
    cnt: undefined, // count field
    
	_init: function(options){
		var self=this;
		
        // set usefull attributes
		this.c=this.element.find('tbody');
		this.o=this.c.children('tr');
		this.dst=$(this.options.dst_field);
		this.cnt=$(this.options.cnt_field);
		
		// get current value of results field
		var ivalue = this.dst.val();
		try{
			if($.parseJSON){
				ivalue=$.parseJSON(ivalue);
				if(!ivalue)ivalue=[];
			}else{
				ivalue=eval('('+ivalue+')')
			}
		}catch(err){
			ivalue=[];
		}
		$.each(ivalue,function(k,v){
			ivalue[k]=String(v);
		});
		
		// initialize JUI Selectable widget
		this.c
            .selectable($.extend({
                filter: 'tr',
                stop: function(){ self.stop.apply(self,[this]) }
            },options))
            .css({cursor:'crosshair'});
        
        // initialize checkboxes
		this.o.find('input[type="checkbox"]')
            .each(function(){
                var o=$(this);
                if($.inArray(o.val(), ivalue)>-1){
                    o.attr('checked',true).closest('tr').addClass('ui-selected');
                }
            })
            .change(function(){
                $(this).closest('tr').toggleClass('ui-selected',$(this).attr('checked'));
                self.recalc();
            });
        
        // force recalc on initialization
        this.recalc();
	},
	stop: function(c){
		$(c).children('.ui-selected').find('input[type="checkbox"]').attr('checked',true);
		$(c).children().not('.ui-selected').find('input[type="checkbox"]:checked').removeAttr('checked',true);
		this.recalc();
	},
	select_all: function(){
        this.o.find('input[type="checkbox"]').removeAttr('disabled');
        this.o.not('.ui-selected')
            .addClass('ui-selected')
            .find('input[type="checkbox"]').attr('checked',true);
        this.recalc();
	},
	unselect_all: function(){
        this.o.find('input[type="checkbox"]').removeAttr('disabled');
        this.o.filter('.ui-selected')
            .removeClass('ui-selected')
            .find('input[type="checkbox"]').removeAttr('checked');
        this.recalc();
	},
	select_star: function(){
        this.unselect_all();
        this.o.find('input[type="checkbox"]').attr('disabled',true);
        if(this.dst) this.dst.val('*');
        if(this.cnt) this.cnt.html('ALL').parent().show();
	},
	recalc: function(){
		var r=[];
		this.o.find('input[type="checkbox"]:checked').each(function(){
			r.push($(this).val());
		});
		if(this.dst) this.dst.val($.univ.toJSON(r));
		if(this.cnt){
            this.cnt.html(r.length?r.length:'')
                .parent().toggle(r.length>0);
		}
	}
});
