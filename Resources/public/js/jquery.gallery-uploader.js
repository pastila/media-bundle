/*
 *  (c) 2019 ИП Рагозин Денис Николаевич. Все права защищены.
 *
 *  Настоящий файл является частью программного продукта, разработанного ИП Рагозиным Денисом Николаевичем
 *  (ОГРНИП 315668300000095, ИНН 660902635476).
 *
 *  Алгоритм и исходные коды программного кода программного продукта являются коммерческой тайной
 *  ИП Рагозина Денис Николаевича. Любое их использование без согласия ИП Рагозина Денис Николаевича рассматривается,
 *  как нарушение его авторских прав.
 *   Ответственность за нарушение авторских прав наступает в соответствии с действующим законодательством РФ.
 */

/*
 * @author Denis N. Ragozin <ragozin at artsofte.ru>
 * @version SVN: $Id$
 * @revision SVN: $Revision$
 */
(function($){
  var defaults = {
    uploadUrl: null,
    onUploadComplete: function() {}
  }
  
  function GalleryUploader(element, options){
    this.options = $.extend({}, defaults, options);
    this.element = $(element);
  
    this.fn = GalleryUploader.fn;
    
    this.initialize();
  
  }
  
  GalleryUploader.fn = GalleryUploader.prototype
  
  $.extend(GalleryUploader.prototype, {
    initialize: function(){
      var self = this;
      
      this.element.dialog({
        autoOpen: false,
        resizable: false,
        width: 810,
        title: 'Добавление изображений',
        open: function(){
          if (self.capabilities == 'html5')
            self.element.parent().find('.ui-dialog-buttonset button:first').hide();          
          self.element.html('');
          self.runtime.embed(self.element, self.options.uploadUrl + "?type="+self.capabilities);
        },
        buttons: {
          'Загрузить изображения': function(){
            
            $(this).parent().find('.ui-dialog-buttonset button:first').hide();
            self.runtime.submit(function(){               
                self.element.dialog('close');
                self.options.onUploadComplete();
            });
          },
          'Закрыть': function(){
            self.element.dialog('close');
          }
        }
      });
      
      this.capabilities = this._getBrowserUploadCapabilities();
      switch (this.capabilities){
        case 'html5': {            
            this.runtime = new HTML5UploaderRuntime({
              onSelectFile: function(){
                self.element.parent().find('.ui-dialog-buttonset button:first').show();
              },
              onRemoveFile: function(){
                if (self.runtime.getQueueLength() == 0){
                  self.element.parent().find('.ui-dialog-buttonset button:first').hide();                  
                }
              }
            }); 
            break;
        }
        case 'legacy': this.runtime = new HTMLUploaderRuntime(); break;         
      }
      
      //this.runtime.embed(this.element);
    },
    _getBrowserUploadCapabilities: function(){
      if ($.support.fileSelecting && $.support.fileReading && $.support.fileSending)
        return 'html5';
      
      return 'legacy';
    },
    open: function(){
      this.element.dialog('open')
    }
  });
  
  $.fn.galleryUploadDialog = function(method){
    var returnValue = this;
    var args = arguments;

    $.each(this, function(){
      var inst = $.data(this, 'galleryUploadDialog');
      if ((typeof method === 'object' || ! method ) && !inst){
        $.data(this, 'galleryUploadDialog', new GalleryUploader(this, method))
      } else if (typeof method === 'string' && method[0] != '_' && inst && inst.fn[method] ) {
        returnValue = inst.fn[method].apply(inst, Array.prototype.slice.call( args, 1 ))
        return false;
      } else {
        $.error( 'Method ' +  method + ' does not exist on jQuery.element' );
      }
    });
    
    return returnValue;
  }
  
})(jQuery)

