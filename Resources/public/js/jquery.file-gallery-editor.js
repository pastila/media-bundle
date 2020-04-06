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
     images: [],
     crop: { },
     pp_settings: {},
     onCrop: null,
     onRemove: null,
     onMove: null,
     onEdit: null
  };



  /**
   * @class {GalleryEditor}
   * 
   */
  function FileGalleryEditor(element, options) {
    
    this.options = $.extend({}, defaults, options);
    this.element = $(element);

    this.fn = this.prototype;
  
    this.initialize();
  }
  
  $.extend(FileGalleryEditor.prototype, {
    
    initialize: function(){
      var self = this;
      var listContainer = $('<div class="gallery-image-list-container"></div>').appendTo(this.element);
      
      this.fileList = $('<ul class="gallery-image-list"></ul>').appendTo(listContainer);
      this.uploaderLink = $('<a href="#" class="file-uploader-link">Загрузить файлы</a>');
      this.element.append(this.uploaderLink);
      this.loader = $('<img src="/bundles/accuratewebmedia/images/ajax-loader.gif" alt="Загрузка"/>').appendTo(this.element).hide();
      
      this._loadFiles();
      this._initUploader();
      
      listContainer.delegate('.gallery-image-remove-link', 'click', function(event){
        var $dialog = $('<div><p style="padding-top: 10px;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Вы уверены, что хотите удалить этот файл?</p></div>');
        var li = $(this).parents('li:first');
        
        $dialog.dialog({
          modal: true,
          title: 'Подтверждение удаления',
          close: function(){ $dialog.remove(); },
          resizable: false,
            buttons: {
              'Да': function(){
                $dialog.dialog('close');

                var imgdata = li.galleryEditorFile('getAllStuff');
                var data = {                  
                          file: {
                            id: imgdata.id,
                            obj: li
                          }
                        }

                if ($.isFunction(self.options.onRemove))        
                  self.options.onRemove(self, data);

                li.galleryEditorFile('destroy');        
                li.remove();              
              },
              'Нет': function(){
                $dialog.dialog('close');
              }
            }          
        })       
      });     
      listContainer.delegate('.gallery-image-edit-link', 'click', function(event){
        event.preventDefault();
         var li = $(this).parents('li:first');
        if ('string' ===  typeof self.options.onEdit){
          var img = $(this).find('img'),
              src = img.attr('src'),
              imgdata = li.galleryEditorFile('getAllStuff');
          img.attr('src', '/asGalleryPlugin/images/ajax-loader-snake.gif');
          $.ajax({
            url: self.options.onEdit,
            data: {
              image_id: imgdata.id
            },
            dataType: 'json',
            success: function(r){
              var $dlg = $('<div></div>').html('<form>'+r.f+'</form>').dialog({
                autoOpen: true,
                width: 400,
                resizable: false,
                title: 'Редактирование файла',
                close: function() { $dlg.dialog('destroy'); $dlg.remove(); },
                buttons: {
                  'OK': function(){
                    var form = $dlg.find('form');
                    $.ajax({
                      url: self.options.onEdit,
                      type: 'post',
                      dataType: 'json',
                      data: form.serialize()+'&image_id='+imgdata.id,
                      success: function(r){
                        if (r.r === true){
                          $dlg.dialog('close');
                        } else {
                          form.html(r.f);
                        }
                      }                      
                    })
                    
                  },
                  'Отмена': function(){
                    $dlg.dialog('destroy');
                  }
                }
              })
            },
            error: function(){
               var $dlg = $('<div>').dialog({
                autoOpen: true,
                resizable: false,
                title: 'Ошибка',
                close: function() { $dlg.dialog('destroy'); $dlg.remove(); },
                buttons: {
                  'OK': function(){
                    $dlg.dialog('close');
                  }
                }
              }).html('<div class="ui-state-error ui-corner-all"><p><span class="ui-icon ui-icon-alert"></span> Не удалось отобразить форму редактирования</p></div>');
            },
            complete: function(){
              img.attr('src', src);
            }
          })          
        }
      })
            
      this.fileList.sortable({
          placeholder: "ui-state-highlight",
          start: function(event, ui){
            self.sorting(true);
          },
          stop: function(event, ui){
            //Добавдяем задержку, чтобы костылировать prettyPhoto, который норовит открыть изображение по клику и никак не отключается
            setTimeout(function() { self.sorting(false) }, 100);
            var li = ui.item;
            if ($.isFunction(self.options.onMove)){
              var imgdata = li.galleryEditorFile('getAllStuff');
              var data = {      
                        position: li.index()+1,
                        file: {
                          id: imgdata.id,
                          obj: li
                        }
                      }
              self.options.onMove(self, data)
            }
          }
      });
      this.fileList.disableSelection();      
    },
    destroy: function(){
      
    },
    _initUploader: function(){
      var self = this;
      
      this.uploadDialog = $('<div></div>').fileGalleryUploadDialog({
        uploadUrl: this.options.uploadUrl,
        onUploadComplete: function(){
          self._loadFiles();
        }
      });
      
      this.uploaderLink.bind('click.file-editor', function(e){
        e.preventDefault();
        self.uploadDialog.fileGalleryUploadDialog('open');
      })
    },
    _loadFiles: function(){
      var fileSource = this.options.files, self = this;
      
      this.fileList.html('');
      if ($.isArray(fileSource)){
        $.each(r, function(){
          self._addFile(this);
        })
      } else if ('string' == typeof fileSource) {        
        this.loader.show();
        this.fileList.hide();
        $.ajax({
          url: fileSource,
          dataType: 'json',
          success: function(r){
            $.each(r, function(){
              self._addFile(this);
            })
          },
          complete: function(){
            self.loader.hide();
            self.fileList.show();
          }
        })
      } else if ($.isFunction(fileSource)) {
        var files = fileSource();
        $.each(files, function(){
          self._addFile( this);
        })
      }
    },
    _addFile: function(file){
      var img = $('<li class="gallery-image-container"><a class="gallery-file-link" href="'+file.src+'" rel="prettyPhoto"><img src="'+file.icon+'" title="" alt="" /><span>'+file.name+'</span></a></li>');
      this.fileList.append(img);
      
      //img.galleryEditorImage($.extend({}, image, { canEdit: null !== this.options.onEdit }));
      img.galleryEditorFile($.extend({}, file, { canEdit: this.options.canEdit }));
    },
    _getUploaderType: function(){
     
    },
    sorting: function(v){
      this.fileList.find('li.gallery-image-container').each(function(){
        $(this).galleryEditorFile('enablePreview', !v);
      });
    }
  })
  
  $.fn.fileGalleryEditor = function(method){
    return this.each(function(){
      var inst = $.data(this, 'fileFalleryEditor');
      
      if ((typeof method === 'object' || ! method ) && !inst){
        $.data(this, 'fileFalleryEditor', new FileGalleryEditor(this, method))
      } else if ('string' == typeof method && method[0] != '_' && inst && inst.fn[method] ) {
        inst.fn[method].apply(this, Array.prototype.slice.call( arguments, 1 ))
      }
    })
  }
  
  var fileDefaults = {
    canEdit: true
  }
  
  /**
   * Вспомогательный класс изображения редактора галереи
   */
  
  function GalleryEditorFile(element, options){
    this.options = $.extend({}, fileDefaults, options);
    this.element = $(element);
    
    this.fn = GalleryEditorFile.prototype;
    
    this.initialize();
  }
  
  $.extend(GalleryEditorFile.prototype, {
    initialize: function(){
      var self = this;
      
      this.icon = this.element.find('img');
      this.iconSrc = this.icon.attr('src');
      
      this.controlHolder = $('<div class="gallery-image-actions"></div>').prependTo(this.element);
      if (self.options.canEdit)
        $('<a class=gallery-image-edit-link><img src="/asGalleryPlugin/images/edit.png" width="16" height="16" alt="Редактировать"/></a>')
        .appendTo(this.controlHolder);
      $('<a class="gallery-image-remove-link"><img src="/asGalleryPlugin/images/remove.png" width="16" height="16" alt="Удалить" /></a>')
        .appendTo(this.controlHolder);
        
    },
    getAllStuff: function(){
      return this.options;
    },
    refresh: function(){
      var d = new Date();
      this.image.attr("src", this.iconSrc+"?"+d.getTime());
    },
    destroy: function(){
      this.element.removeData('galleryEditorFile');
      this.controlHolder.remove();
    },
    enablePreview: function(v){
      this.previewEnabled = v;
    }
  });
  
  $.fn.galleryEditorFile = function(method){    
    var returnValue = this;
    var args = arguments;

    $.each(this, function(){
      var inst = $.data(this, 'galleryEditorFile');
      if ((typeof method === 'object' || ! method ) && !inst){
        $.data(this, 'galleryEditorFile', new GalleryEditorFile(this, method))
      } else if (typeof method === 'string' && method[0] != '_' && inst && inst.fn[method] ) {
        returnValue = inst.fn[method].apply(inst, Array.prototype.slice.call( args, 1 ))
        return false;
      } else {
        $.error( 'Method ' +  method + ' does not exist on jQuery.element' );
      }
    });
    
    return returnValue;
  }
})(jQuery);

