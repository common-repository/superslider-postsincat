/*
Script: slideBox.js
	slideBox - slideBox version 1.0

License:
	MIT-style license.

Copyright:
	Copyright 2008
	Riaan Los
	http://www.riaanlos.nl

Based on MooTools v1.2 Core
	[The MooTools production team](http://mootools.net/developers/).


Usage:
	window.addEvent('domready', function() {
		var example1 = new slidePosts('example1');
		var example2 = new slidePosts('example2',{
			speed:10,
			removeArrows:false,
			transition:Fx.Transitions.Bounce.easeOut,
			fadeArrows:true,
			startOpacity:0.4
		});
	});

Keep smiling ;)
*/

var slidePosts = new Class({

	Implements: [Events, Options],

	options: {
		className:'slideBox',					// className (XHTML + CSS)
		prevArrow:'-previous',					// previous arrow className suffix
		nextArrow:'-next',						// next arrow className suffix
		removeArrows:true,						// remove arrows on top/bottom
		fadeArrows:false,						// add fade effect to arrows
		startOpacity:0.5,						// start opacity for fade effect on arrows
		endOpacity:1,							// end opacity for fade effect on arrows
		mouseoverBox:true,						// add/remove mouseenter class on UL LI elements inside slider
		startClass:'normal',					// className at mouseout
		endClass:'over',						// className at mouseenter
		speed:5,								//	scroll speed
		transition:Fx.Transitions.Quart.easeOut, // scrolling transition
		myaction:'click' //click
	},

	initialize: function(element,options){
		element = $(element);
		this.setOptions(options);		
		this.active = false;

		this.up = element.getElements('div[class=' + this.options.className + '-previous]');
		this.up = this.up[0].getElements('a');
		this.up = this.up[0];
		
		this.down = element.getElements('div[class=' + this.options.className + '-next]');
		this.down = this.down[0].getElements('a');
		this.down = this.down[0];
		
		this.wrapper = element.getElements('div[class=' + this.options.className + '-wrapper]');
		this.wrapperH = this.wrapper[0].getStyle('height').toInt();
		
		this.slider = this.wrapper[0].getElements('div[class=' + this.options.className + '-slider]');
		this.slider = this.slider[0];
		
		
		if(this.options.removeArrows) this.removeArrows();
		if(this.options.fadeArrows) this.fadeArrows();
		if(this.options.mouseoverBox) this.mouseoverBox();
		
		this.clickEvent(element);
	},
	
	removeArrows: function() {
		this.start = this.slider.getStyle('top').toInt();			
		if(this.start==0) this.up.getParent().setStyle('display','none');
	},
	
	fadeArrows: function(){
		this.up.setStyle('opacity',this.options.startOpacity);
		this.down.setStyle('opacity',this.options.startOpacity);
		
		this.up.addEvent('mouseenter', this.up.fade.bind(this.up,[this.options.endOpacity]));
		this.down.addEvent('mouseenter', this.down.fade.bind(this.down,[this.options.endOpacity]));
		
		this.up.addEvent('mouseleave', this.up.fade.bind(this.up,[this.options.startOpacity]));
		this.down.addEvent('mouseleave', this.down.fade.bind(this.down,[this.options.startOpacity]));
	},
	
	mouseoverBox: function(){
		$$('.' + this.options.className + '-slider UL LI').each(function(element,index){
			element.addClass(this.options.startClass);
			element.addEvent('mouseenter',function(){
				element.addClass(this.options.endClass);
				element.removeClass(this.options.startClass);
			}.bind(this));
			element.addEvent('mouseleave',function(){
				element.addClass(this.options.startClass);
				element.removeClass(this.options.endClass);
			}.bind(this));
		}.bind(this));
	},
	
	setArrows: function() {
		this.current = this.slider.getStyle('top').toInt();
		this.last = 0-(((this.height/this.wrapperH)-1)*this.wrapperH);
		if(this.current==0) { 
			this.up.getParent().setStyle('display','none');
			this.down.getParent().setStyle('display','block');
		} else if(this.current > this.last) {
			this.up.getParent().setStyle('display','block');
			this.down.getParent().setStyle('display','block');
		} else {
			this.up.getParent().setStyle('display','block');
			this.down.getParent().setStyle('display','none');
		}
	},
	
	clickEvent: function(element){		
		this.height = this.slider.getSize().y;		
		this.slideFx = new Fx.Tween(this.slider,{
			duration:(this.options.speed*100),
			transition:this.options.transition,
			wait:false,
			onComplete:function(){
				this.active = false;
				if(this.options.removeArrows) this.setArrows();
			}.bind(this)
		});
		
		this.up.addEvent(this.options.myaction,function(e){
			var e = new Event(e).stop();
			if(this.active==false) {
				this.scrollUp();
			}
		}.bind(this));
		
		this.down.addEvent(this.options.myaction,function(e){
			var e = new Event(e).stop();
			if(this.active==false) {
				this.scrollDown();
			}
		}.bind(this));
	},
	
	scrollDown: function(){		
		this.now = this.slider.getStyle('top').toInt();		
		this.last = 0-(((this.height/this.wrapperH)-1)*this.wrapperH);
		if(this.now > this.last) {
			this.active = true;
			this.slideFx.start('top',(this.slider.getStyle('top').toInt()-this.wrapperH)+'px');
		}
	},
	
	scrollUp: function() {
		this.now = this.slider.getStyle('top').toInt();
		this.last = 0-(((this.height/this.wrapperH)-1)*this.wrapperH);		
		if(this.now < 0) {
			this.active = true;
			this.slideFx.start('top',(this.now+this.wrapperH)+'px');
		}
	}

});