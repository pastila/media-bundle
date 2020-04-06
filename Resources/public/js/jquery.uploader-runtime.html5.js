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
function HTML5UploaderRuntime(options){
  this.options = $.extend({
    onSelectFile: null,
    onRemoveFile: null
  }, options);
}

(function($){
  
  var templates = {
    global: '<div class="ig-uploader">\n\
                      <div class="igu-droparea">\n\
                          <div class="igu-notice">Чтобы добавить файлы для загрузки, перетащите их сюда или <a class="igu-select-link" href="">выберите из списка</a></div>\n\
                          <ul class="igu-imagelist"></ul>\n\
                      </div>\n\
                      <input type="file" name="images" multiple="" style="display:none" />\n\
                      <input type="hidden" name="type" value="html5"/>\n\
                      <img class="igu-ajax-loader" src="/asGalleryPlugin/images/ajax-loader.gif" alt="Загрузка" style="display:none"/>  \n\
                      <div class="igu-overlay"></div>\n\
            </div>',      
   image: '<li>\n\
            <a class="igu-remove"><img src="/asGalleryPlugin/images/remove.png" width="16" height="16" alt="Удалить изображение" /></a>\n\
            <div><img class="igu-preview"/></div>\n\
          </li>',
   file:  '<li>\n\
            <a class="igu-remove"><img src="/asGalleryPlugin/images/remove.png" width="16" height="16" alt="Удалить Файл" /></a>\n\
            <div><img class="igu-preview"/><span class="igu-filename"></span></div>\n\
          </li>'
  };
  
  HTML5UploaderRuntime.fn = HTML5UploaderRuntime.prototype;
  
  $.extend(HTML5UploaderRuntime.fn, {
    embed: function(element, url) {
      var self = this;

      var template = $(templates.global);
   
      this.element = element;
      this.element.append(template);
      this.uploadCallback = null;
      
      this.input = $('input[type=file]', template);
      this.imageList = $('.igu-imagelist', template);
      
      $('.igu-select-link', template).bind('click.igu', function(e){
        e.preventDefault();
        self.input.click();
      })
      
      template.bind({
        dragenter: function() {
          return false;
        },
        dragover: function() {
          return false;
        },
        dragleave: function() {
          return false;
        }
      });
      
      this.input.damnUploader({
        url: url,
        dropBox: $('.igu-droparea', template),
        fieldName: "image",
        onSelect: function(file) {
          self.addFileToQueue(file);
          if ($.isFunction(self.options.onSelectFile))                     
            self.options.onSelectFile();
          return false;
        },
        onAllComplete: function(){
          if ($.isFunction(self.uploadCallback))
            self.uploadCallback();
          self.uploadCallback = null;
          $('.igu-ajax-loader').hide();
        }
      })
      
      this.uploadLink = $('.igu-submit', template).button();
    },
    addFileToQueue: function(file) {   
      
      var self = this,
          is_image = file.type.match(/image\/.*/),
          template = is_image ? $(templates.image) : $(templates.file);
  
      self.imageList.append(template);

      if (is_image){
        if($.support.fileReading) {
          var Reader = new FileReader();
          Reader.onload = (function(Preview) {
            return function(e) {
            Preview.attr('src', e.target.result);
            Preview.attr('width', 90);                                        
            };
          })($('img.igu-preview', template));
          Reader.readAsDataURL(file);
        }    
      } else {
        var ext = file.name.split('.').pop().toLowerCase(),
            iconUrlPrefix = '/asGalleryPlugin/images/file-icons/',
            iconMap = {
              'doc': iconUrlPrefix + 'doc.png',
              'pdf': iconUrlPrefix + 'pdf.png'
            },
            icon = iconUrlPrefix +  '_blank.png';
        if ('undefined' !== typeof iconMap[ext]){
          icon = iconMap[ext];
        }
        
        template.find('img.igu-preview').attr('src', icon);
        template.find('.igu-filename').text(file.name);
      }

      var uploadItem = {
        file: file,
        onProgress: function(percents) {
          // здесь можно сделать прогрессбар
        },
        onComplete: function(successfully, data, errorCode) {
          if(successfully) {
            data = data ? $.parseJSON(data) : false;
            if (data && data.error == true) {
              alert('Изображение: ' + data.filename + '\nОшибка: ' + data.message);
            }
          } else {
            if(!this.cancelled) {
            }
          }
        }
      };        

      // получаем идентификатор загружаемого файла    
      var queueId = self.input.damnUploader('addItem', uploadItem);

      // событие для ссылки отмены    
      $(".igu-remove", template).click(function() {
        self.input.damnUploader('cancel', queueId);
        template.remove();
        
        if ($.isFunction(self.options.onRemoveFile))
          self.options.onRemoveFile();
        return false;
      });

      return uploadItem;
    },
    submit: function(cb){
      if (!this.uploadCallback){
        $('.igu-ajax-loader, .igu-overlay', this.element).show();
        $('.igu-notice').hide();
        
        this.element.find('.igu-overlay').css({
          height: $('.igu-droparea', this.element).height()
        });
        
        this.input.damnUploader('startUpload');
        this.uploadCallback = cb;
      }
    },
    getQueueLength: function(){
      return this.input.damnUploader('itemsCount');
    }
  });
  
})(jQuery)
