$(function () {

        slide("#container", 0, function (e) {
            var that = this;

            setTimeout(function () {
                that.back.call();
            }, 0);

        });
    });
	/*
    *obj--        
    *offset--       루            ڵ   offsetʱ      callback  
    *callback--      ɺ Ļص     
    */
    var slide = function (obj, offset, callback) {
        var start,
            end,
            isLock = false,// Ƿ             
            isCanDo = false,// Ƿ  ƶ     
            isTouchPad = (/hp-tablet/gi).test(navigator.appVersion),
            hasTouch = 'ontouchstart' in window && !isTouchPad;

        //      ת  Ϊjquery Ķ   
        obj = $(obj);

        var objparent = obj.parent();

        /*        */
        var fn =
            {
                // ƶ     
                translate: function (diff) {
                    obj.css({
                        "-webkit-transform": "translate(0," + diff + "px)",
                        "transform": "translate(0," + diff + "px)"
                    });
                },
                //    Ч  ʱ  
                setTranslition: function (time) {
                    obj.css({
                        "-webkit-transition": "all " + time + "s",
                        "transition": "all " + time + "s"
                    });
                },
                //   ص   ʼλ  
                back: function () {
                    fn.translate(0 - offset);
                    //  ʶ       
                    isLock = false;
                }
            };

        //      ʼ
        obj.bind("touchstart", function (e) {

            if (objparent.scrollTop() <= 0 && !isLock) {
                var even = typeof event == "undefined" ? e : event;
                //  ʶ          
                isLock = true;
                isCanDo = true;
                //   浱ǰ   Y    
                start = hasTouch ? even.touches[0].pageY : even.pageY;
                //       鶯  ʱ  
                fn.setTranslition(0);
            }
        });

        //      
        obj.bind("touchmove", function (e) {

            if (objparent.scrollTop() <= 0 && isCanDo) {

                var even = typeof event == "undefined" ? e : event;

                //   浱ǰ   Y    
                end = hasTouch ? even.touches[0].pageY : even.pageY;

                if (start < end) {
                    even.preventDefault();
                    //       鶯  ʱ  
                    fn.setTranslition(0);
                    // ƶ     
                    fn.translate(end - start - offset);
                }

            }
        });


        //        
        obj.bind("touchend", function (e) {
            if (isCanDo) {
                isCanDo = false;
                // жϻ        Ƿ   ڵ   ָ  ֵ
                if (end - start >= offset) {
                    //   û   ص ʱ  
                    fn.setTranslition(0.5);
                    //      ʾ    
                    fn.translate(0);

                    //ִ лص     
                    if (typeof callback == "function") {
                        callback.call(fn, e);
                    }
                } else {
                    //   س ʼ״̬
                    fn.back();
                }
            }
        });
    }