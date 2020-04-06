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
    pp_settings: {
      social_tools: false
    },
    onCrop: null,
    onRemove: null,
    onMove: null,
    onEdit: null
  };

  var cropDefaults = {
    allowSelect: false
  }

  /**
   * @class {GalleryEditor}
   *
   */
  function GalleryEditor(element, options) {

    this.options = $.extend({}, defaults, options);
    this.element = $(element);

    this.fn = this.prototype;

    this.initialize();
  }

  $.extend(GalleryEditor.prototype, {

    initialize: function(){
      var self = this;
      var listContainer = $('<div class="gallery-image-list-container"></div>').appendTo(this.element);

      this.imageList = $('<ul class="gallery-image-list"></ul>').appendTo(listContainer);
      this.uploaderLink = $('<a href="#" class="gallery-uploader-link">Загрузить изображения</a>');
      this.element.append(this.uploaderLink);
      this.loader = $('<img src="/bundles/accuratewebmedia/images/ajax-loader.gif" alt="Загрузка"/>').appendTo(this.element).hide();

      this._loadImages();
      this._initUploader();

      listContainer.delegate('.gallery-image-crop-link', 'click', function(event){
        event.preventDefault();

        var jcrop_api;
        var li = $(this).parents('li:first');
        var $dialog = $('<div><div class="jc-dialog"><img src="'+li.find('a.gallery-image-full').attr('href')+'" /></div></div>');

        var cropCoords = li.galleryEditorImage('getCrop');

        var cropOptions = $.extend({}, cropDefaults, self.options.crop, {
          onChange: function(c) {
            cropCoords = [c.x, c.y, c.x + c.w, c.y + c.h]
          }
        });

        if (null === cropCoords[0]) {
          cropOptions.allowSelect = true;
        } else {
          cropOptions.setSelect = cropCoords;
        }

        var imgdata = li.galleryEditorImage('getAllStuff'),
          boxWidth = 0;

        if (typeof self.options.crop.boxWidth !== 'undefined')

        $dialog.find('img').Jcrop(cropOptions, function(){
          jcrop_api = this;
          $dialog.dialog({
            modal: true,
            title: 'Изменение изображения',
            close: function(){ $dialog.remove(); },
            width: (boxWidth > 0 ? boxWidth : jcrop_api.getBounds()[0]) + 34,
            resizable: false,
            buttons: {
              'OK': function(){
                var data = {
                  coords: cropCoords,
                  image: {
                    id: imgdata.id,
                    obj: li
                  }
                }
                if ($.isFunction(self.options.onCrop))
                  self.options.onCrop(self, data);
                li.galleryEditorImage('setCrop', cropCoords);
                $dialog.dialog('close');
              },
              'Отмена': function(){
                $dialog.dialog('close');
              }
            }
          })
        });

      });
      listContainer.delegate('.gallery-image-remove-link', 'click', function(event){
        var $dialog = $('<div><p style="padding-top: 10px;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Вы уверены, что хотите удалить это изображение?</p></div>');
        var li = $(this).parents('li:first');

        $dialog.dialog({
          modal: true,
          title: 'Подтверждение удаления',
          close: function(){ $dialog.remove(); },
          resizable: false,
          buttons: {
            'Да': function(){
              $dialog.dialog('close');

              var imgdata = li.galleryEditorImage('getAllStuff');
              var data = {
                image: {
                  id: imgdata.id,
                  obj: li
                }
              }

              if ($.isFunction(self.options.onRemove))
                self.options.onRemove(self, data);

              li.galleryEditorImage('destroy');
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
            imgdata = li.galleryEditorImage('getAllStuff');
          img.attr('src', '/bundles/accuratewebmedia/images/ajax-loader-snake.gif');
          $.ajax({
            url: self.options.onEdit,
            data: {
              image_id: imgdata.id
            },
            dataType: 'json',
            success: function(r){
              var $dlg = $('<div></div>').html('<form>'+r.f+'</form>').dialog({
                autoOpen: true,
                title: 'Настройки изображения',
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

      this.imageList.sortable({
        placeholder: "ui-state-highlight",
        start: function(event, ui){
          self.sorting(true);
        },
        stop: function(event, ui){
          //Добавдяем задержку, чтобы костылировать prettyPhoto, который норовит открыть изображение по клику и никак не отключается
          setTimeout(function() { self.sorting(false) }, 100);
          var li = ui.item;
          if ($.isFunction(self.options.onMove)){
            var imgdata = li.galleryEditorImage('getAllStuff');
            var data = {
              position: li.index()+1,
              image: {
                id: imgdata.id,
                obj: li
              }
            }
            self.options.onMove(self, data)
          }
        }
      });
      this.imageList.disableSelection();

      //Грязный хак для кривого prettyPhoto, задающий для него настройки по умолчанию
      $().prettyPhoto(this.options.pp_settings);
    },
    destroy: function(){

    },
    _initUploader: function(){
      var self = this;

      this.uploadDialog = $('<div></div>').galleryUploadDialog({
        uploadUrl: this.options.uploadUrl,
        onUploadComplete: function(){
          self._loadImages();
        }
      });

      this.uploaderLink.bind('click.gallery-editor', function(e){
        e.preventDefault();
        self.uploadDialog.galleryUploadDialog('open');
      })
    },
    _loadImages: function(){
      var imageSource = this.options.images, self = this;

      this.imageList.html('');
      if ($.isArray(imageSource)){
        $.each(r, function(){
          self._addImage(this);
        })
      } else if ('string' == typeof imageSource) {
        this.loader.show();
        this.imageList.hide();
        $.ajax({
          url: imageSource,
          dataType: 'json',
          success: function(r){
            $.each(r, function(){
              self._addImage(this);
            })
          },
          complete: function(){
            self.loader.hide();
            self.imageList.show();
          }
        })
      } else if ($.isFunction(imageSource)) {
        var images = imageSource();
        $.each(images, function(){
          self._addImage( this);
        })
      }
    },
    _addImage: function(image){
      var img = $('<li class="gallery-image-container"><a class="gallery-image-full" href="'+image.src+'" rel="prettyPhoto"><img src="'+image.preview.src+'" title="" alt="" /></a></li>');
      this.imageList.append(img);

      //img.galleryEditorImage($.extend({}, image, { canEdit: null !== this.options.onEdit }));
      img.galleryEditorImage($.extend({}, image, { canEdit: this.options.canEdit }));
    },
    _getUploaderType: function(){

    },
    sorting: function(v){
      this.imageList.find('li.gallery-image-container').each(function(){
        $(this).galleryEditorImage('enablePreview', !v);
      });
    }
  })

  $.fn.galleryEditor = function(method){
    return this.each(function(){
      var inst = $.data(this, 'galleryEditor');

      if ((typeof method === 'object' || ! method ) && !inst){
        $.data(this, 'galleryEditor', new GalleryEditor(this, method))
      } else if ('string' == typeof method && method[0] != '_' && inst && inst.fn[method] ) {
        inst.fn[method].apply(this, Array.prototype.slice.call( arguments, 1 ))
      }
    })
  }

  var imageDefaults = {
    crop: [0, 0, 0, 0],
    canEdit: true
  }

  /**
   * Вспомогательный класс изображения редактора галереи
   */

  function GalleryEditorImage(element, options){
    this.options = $.extend({}, imageDefaults, options);
    this.element = $(element);

    this.fn = GalleryEditorImage.prototype;

    this.initialize();
  }

  $.extend(GalleryEditorImage.prototype, {
    initialize: function(){
      var self = this;

      this.previewEnabled = true;

      this.element.find('a').bind('click', function(e){
        e.preventDefault();
        /*
         * Отключаем всплытие события, чтобы предотвратить открывание prettyPhoto,
         * так как саму по себе эту кривую хрень нельзя отключить
         */
        if (self.previewEnabled)
          $.prettyPhoto.open([$(this).attr('href')]);

      });

      this.image = this.element.find('img');
      this.imageSrc = this.image.attr('src');

      this.controlHolder = $('<div class="gallery-image-actions"></div>').prependTo(this.element);
      $('<a class="gallery-image-crop-link"><img src="/bundles/accuratewebmedia/images/crop.png" width="16" height="16" alt="Обрезать изображение" /></a>')
        .appendTo(this.controlHolder);
      if (self.options.canEdit)
        $('<a class=gallery-image-edit-link><img src="/bundles/accuratewebmedia/images/edit.png" width="16" height="16" alt="Параметры изображения"/></a>')
          .appendTo(this.controlHolder);
      $('<a class="gallery-image-remove-link"><img src="/bundles/accuratewebmedia/images/remove.png" width="16" height="16" alt="Удалить изображение" /></a>')
        .appendTo(this.controlHolder);

    },
    getCrop: function(){
      return this.options.crop
    },
    setCrop: function(coords){
      this.options.crop = coords;
    },
    getAllStuff: function(){
      return this.options;
    },
    refresh: function(){
      var d = new Date();
      this.image.attr("src", this.imageSrc+"?"+d.getTime());
    },
    destroy: function(){
      this.element.removeData('galleryEditorImage');
      this.controlHolder.remove();
    },
    enablePreview: function(v){
      this.previewEnabled = v;
    }
  });

  $.fn.galleryEditorImage = function(method){
    var returnValue = this;
    var args = arguments;

    $.each(this, function(){
      var inst = $.data(this, 'galleryEditorImage');
      if ((typeof method === 'object' || ! method ) && !inst){
        $.data(this, 'galleryEditorImage', new GalleryEditorImage(this, method))
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

