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
function HTMLUploaderRuntime(options){
//  this.options = $.extend({
//    onSelectFile: null,
//    onRemoveFile: null
//  }, options);
}

(function($){
  var templates = {
    global: '<div class="ig-uploader">\n\
               <form action="" method="POST" enctype="multipart/form-data"\n\
               </form>\n\
             </div>',
    file: '<input type="file" name="" />'
  }
  
  HTMLUploaderRuntime.fn = HTMLUploaderRuntime.prototype;
  
  $.extend(HTMLUploaderRuntime.fn, {
    embed: function(element, url) {
      var self = this;

      var template = $(templates.global);
   
      this.element = element;
      this.element.append(template);
      
      this.form = template.find('form');
      this.form.attr('action', url)
      
      for (var i = 0; i < 3; i++){
        var fileTemplate = $(templates.file).appendTo(this.form)
        fileTemplate.attr('name', 'legacy_multiupload[images_'+i+'][image]')
      }
    },
    submit: function(cb){
      this.form.submit();      
    }
  });
  
})(jQuery)

