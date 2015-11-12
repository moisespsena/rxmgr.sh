(function($) {
    $.fn.getAttributes = function() {
        var attributes = {}; 

        if( this.length ) {
            $.each( this[0].attributes, function( index, attr ) {
                if(attr.name.indexOf('data-') != -1) {
                    attributes[ attr.name.replace(/data-/i, '') ] = attr.value;
                }
            } ); 
        }

        return attributes;
    };
    $.fn.formData = function() {
        var data = {};
        if (this.length) {
            var arr = $(this).serializeArray();
            for(var k in arr) {
                data[arr[k].name] = arr[k].value;
            }
        }
        return data;
    };

})(jQuery);

function setTemplate(name, options) {
    data = $('#tpl_' + name).html();
    for(var k in options) {
         data = data.replace(new RegExp('\\{' + k + '\\}', 'g'), options[k]);
    }
    
    var $t = $(data);
    $('#main').hide();
    $('#panel').html($t).show();
    var $cancel = $t.find('.cancel');

    $cancel.click(closePanel);
    return $t;
};

function closePanel() {
    $('#panel').html('').hide();
    $('#main').show();
};

function validate(data, options) {
    for(var k in options) {
        var o = options[k];
        if(!(k in data)) {
            if(o.required) {
                alert("O campo '" + o.title + "' é de preenchimento obrigatório");
                return false;
            }
            continue;
        }
        var d = data[k];
        if(('validate' in o)) {
            if(!o.validate({val: d, opt: o, opts: options, data: data})) {
                return false;
            }
        }
    }
    return true;
};

var DEFAULT_VALIDATIONS = {
    password: {
        required: true,
        title: 'Senha',
        validate: function(o) {
            if(o.val.length < 6) {
                alert("A senha precisa ter no mínimo 6 caracteres");
                return false;
            }
            return true;
        }
    },
    width: {
        required: true,
        title: 'Largura',
        validate: function(o) {
            if(!/^\d+$/.test(o.val)) {
                alert("A Largura precisa ser um número inteiro");
                return false;
            }
            if(o.val.replace(/^0+/, '') != o.val) {
                alert("Valor inválido para Largura.");
                return false;
            }
            return true;
        }
    },
    height: {
        required: true,
        title: 'Altura',
        validate: function(o) {
            if(!/^\d+$/.test(o.val)) {
                alert("A Altura precisa ser um número inteiro");
                return false;
            }
            if(o.val.replace(/^0+/, '') != o.val) {
                alert("Valor inválido para Largura.");
                return false;
            }
            return true;
        }
    }
};

var DV = DEFAULT_VALIDATIONS;

function mkVals(items) {
    var vals = {};
    for(var k in items) {
        vals[items[k]] = DV[items[k]];
    }
    return vals;
};

function executeAction(data, opts) {
    opts = $.extend({ success: null, refresh: false, begin: null, complete: null}, opts || {});
    if(opts.begin) {
        opts.begin();
    }
    $.ajax({
        url: location.href,
        data: data,
        method: 'post',
        success: function(data) {
        eval('data = ' + data + ';');

        if(data.error) {
            console.log(data);
            alert('ERRO:\n\n' + (data.message || data.data));
        } else {
            if(data.data) {
                alert(data.data);
            }
            if (opts.success) {
                opts.success(data);
            }
            if(data.refresh || opts.refresh) {
                window.location.href = window.location.href.split('#', 1)[0];
            }
        }
        }, error: function(e) {
            alert(e);
        },
        complete: function() {
            if(opts.complete) {
                opts.complete()
            }
        }
    });
};

var ACTIONS = {
    create_display: function($e, options) {
        var $doc = $(document);
        var $t = setTemplate(options.action, options);
        var $pwd = $t.find('[name=password]');
        var $w = $t.find('[name=width]');
        $w.val($doc.width());
        var $h = $t.find('[name=height]');
        $h.val($doc.height());
        var $setScreenSize = $t.find('.set-screen-size');
        $setScreenSize.click(function() {
            $w.val(screen.width);
            $h.val(screen.height);
        });
        $t.find('.ok').click(function(e) {
             var $ok = $(this);
             try {
                 var data = $t.formData();
                 
                 if(validate(data, mkVals('password width height'.split(' ')))) {
                     data = $.extend(data, options);
                     executeAction(data, {
                         refresh: true,
                         success: function() {
                             $pwd.val('');
                         },
                         begin: function() {
                             $ok.attr('disabled', 'disabled');
                         },
                         complete: function() {
                             $ok.removeAttr('disabled');
                         }
                     });
                 }
             } catch(e) {
                 console.log(e);
             } finally {
                 e.preventDefault();
                 return false;
             }
        });
    },
    close_display: function($e, options) {
        var $doc = $(document);
        var $t = setTemplate(options.action, options);
        var $pwd = $t.find('[name=password]');
        $t.find('.ok').click(function(e) {
             var $ok = $(this);
             try {
                 var data = $t.formData();
                 
                 if(validate(data, mkVals(['password']))) {
                     data = $.extend(data, options);
                     executeAction(data, {
                         refresh: true,
                         success: function() {
                             $pwd.val('');
                         },
                         begin: function() {
                             $ok.attr('disabled', 'disabled');
                         },
                         complete: function() {
                             $ok.removeAttr('disabled');
                         }
                     });
                 }
             } catch(e) {
                 console.log(e);
             } finally {
                 e.preventDefault();
                 return false;
             }
        });
    }

};


$(function() {
    $('[data-action]').click(function() {
        var attrs = $(this).getAttributes();
        if((attrs.action in ACTIONS)) {
            ACTIONS[attrs.action]($(this), attrs);
        }
    });
});
