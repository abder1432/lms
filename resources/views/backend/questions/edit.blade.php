@extends('backend.layouts.app')
@section('title', __('labels.backend.questions.title').' | '.app_name())

@section('content')

    {!! Form::model($question, ['method' => 'PUT', 'route' => ['admin.questions.update', $question->id], 'files' => true,]) !!}

    <div class="card">
        <div class="card-header">
            <h3 class="page-title float-left mb-0">@lang('labels.backend.questions.edit')</h3>
            <div class="float-right">
                <a href="{{ route('admin.questions.index') }}"
                   class="btn btn-success">@lang('labels.backend.questions.view')</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('question',  trans('labels.backend.questions.fields.question').'*', ['class' => 'control-label']) !!}
                    {!! Form::textarea('question', old('question'), ['class' => 'form-control ', 'placeholder' => '', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('question'))
                        <p class="help-block">
                            {{ $errors->first('question') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                @if ($question->question_image)
                    <div class="col-9">
                        {!! Form::label('question_image', trans('labels.backend.questions.fields.question_image'), ['class' => 'control-label']) !!}
                        {!! Form::file('question_image', ['class' => 'form-control', 'style' => 'margin-top: 4px;']) !!}
                        {!! Form::hidden('question_image_max_size', 8) !!}
                        {!! Form::hidden('question_image_max_width', 4000) !!}
                        {!! Form::hidden('question_image_max_height', 4000) !!}
                        <p class="help-block"></p>
                        @if($errors->has('question_image'))
                            <p class="help-block">
                                {{ $errors->first('question_image') }}
                            </p>
                        @endif
                    </div>

                    <div class="col-1 form-group">
                        <a href="{{ asset('storage/uploads/'.$question->question_image) }}" target="_blank">
                            <img height="70px" src="{{ asset('storage/uploads/'.$question->question_image) }}"></a>
                    </div>
                @else
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('question_image', trans('labels.backend.questions.fields.question_image'), ['class' => 'control-label']) !!}
                    {!! Form::file('question_image', ['class' => 'form-control', 'style' => 'margin-top: 4px;']) !!}
                    {!! Form::hidden('question_image_max_size', 8) !!}
                    {!! Form::hidden('question_image_max_width', 4000) !!}
                    {!! Form::hidden('question_image_max_height', 4000) !!}
                    <p class="help-block"></p>
                    @if($errors->has('question_image'))
                        <p class="help-block">
                            {{ $errors->first('question_image') }}
                        </p>
                    @endif
                </div>
                @endif

            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('score', trans('labels.backend.questions.fields.score').'*', ['class' => 'control-label']) !!}
                    {!! Form::number('score', old('score'), ['class' => 'form-control', 'placeholder' => '', 'required' => '']) !!}
                    <p class="help-block"></p>
                    @if($errors->has('score'))
                        <p class="help-block">
                            {{ $errors->first('score') }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-12 form-group">
                    {!! Form::label('tests', trans('labels.backend.questions.fields.tests'), ['class' => 'control-label']) !!}
                    {!! Form::select('tests[]', $tests, old('tests') ? old('tests') : $question->tests->pluck('id')->toArray(), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required' => true]) !!}
                    <p class="help-block"></p>
                    @if($errors->has('tests'))
                        <p class="help-block">
                            {{ $errors->first('tests') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($question->options->count())
    {!! Form::hidden('options_available', 1) !!}
    <div id="main-container">
        @foreach ($question->options as $key=>$option)
            <div class="panel card container-item">
            <div class="panel-body p-4">
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option['.$key.'][text]', trans('labels.backend.questions.fields.option_text').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('option['.$key.'][text]', $option->option_text, ['class' => 'form-control ', 'rows' => 3, 'required' => true]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option['.$key.'][text]'))
                            <p class="help-block">
                                {{ $errors->first('option['.$key.'][text]') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option['.$key.'][explanation]', trans('labels.backend.questions.fields.option_explanation').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('option['.$key.'][explanation]', $option->explanation, ['class' => 'form-control ', 'rows' => 3]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option['.$key.'][explanation]'))
                            <p class="help-block">
                                {{ $errors->first('option['.$key.'][explanation]') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option['.$key.'][correct]', trans('labels.backend.questions.fields.correct'), ['class' => 'control-label']) !!}
                        {!! Form::hidden('option['.$key.'][correct]', 0) !!}
                        {!! Form::hidden('option['.$key.'][correct]',  $option->id ) !!}
                        {!! Form::checkbox('option['.$key.'][correct]', 1, ($option->correct == 1) ? true : false, []) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option['.$key.'][correct]'))
                            <p class="help-block">
                                {{ $errors->first('option['.$key.'][correct]') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div>
                            <a href="javascript:void(0)" class="remove-item btn btn-sm btn-danger remove-social-media">Remove option</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="card p-2">
        <div>
            <a class="btn btn-success" id="add-more" href="javascript:;" role="button"><i class="fa fa-plus"></i> Add option</a>
        </div>
    </div>
    @else
    {!! Form::hidden('options_available', 0) !!}
    <div id="main-container">
        <div class="panel card container-item">
            <div class="panel-body p-4">
                <div class="row">
                    <div class="col-6 form-group">
                        {!! Form::label('option[0][text]', trans('labels.backend.questions.fields.option_text').'*', ['class' => 'control-label']) !!}
                        {!! Form::textarea('option[0][text]', old('option[0][text]'), ['class' => 'form-control ', 'rows' => 3, 'required' =>  true]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option[0][text]'))
                            <p class="help-block">
                                {{ $errors->first('option[0][text]') }}
                            </p>
                        @endif
                    </div>
                    <div class="col-6 form-group">
                        {!! Form::label('option[0][explanation]', trans('labels.backend.questions.fields.option_explanation'), ['class' => 'control-label']) !!}
                        {!! Form::textarea('option[0][explanation]', old('option[0][explanation]'), ['class' => 'form-control ', 'rows' => 3]) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option[0][explanation]'))
                            <p class="help-block">
                                {{ $errors->first('option[0][explanation]') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 form-group">
                        {!! Form::label('option[0][correct]', trans('labels.backend.questions.fields.correct'), ['class' => 'control-label']) !!}
                        {!! Form::hidden('option[0][correct]', 0) !!}
                        {!! Form::checkbox('option[0][correct]', 1, false, []) !!}
                        <p class="help-block"></p>
                        @if($errors->has('option[0][correct]'))
                            <p class="help-block">
                                {{ $errors->first('option[0][correct]') }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
                        <div>
                            <a href="javascript:void(0)" class="remove-item btn btn-sm btn-danger remove-social-media">Remove option</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card p-2">
        <div>
            <a class="btn btn-success" id="add-more" href="javascript:;" role="button"><i class="fa fa-plus"></i> Add option</a>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-12 text-center mb-4">
            {!! Form::submit(trans('strings.backend.general.app_update'), ['class' => 'btn btn-danger']) !!}

        </div>
    </div>


    {!! Form::close() !!}
@stop

@push('after-scripts')
    <script>
        /**
         * Instructions: Call $(selector).cloneData(options) on an element with a jQuery type selector
         * defined in the attribute "rel" tag. This defines the DOM element to copy.
         *
         * @CreadtedBY Rajneesh Gautam
         * @CreadtedOn 24/07/2019
         *
         @example:
         $('a#add-education').cloneData({
            mainContainerId: 'main-container', // Main container Should be ID
            cloneContainer: 'clone-container', // Which you want to clone
            removeButtonClass: 'remove-education', // Remove button for remove cloned HTML
            removeConfirm: true, // default true confirm before delete clone item
            removeConfirmMessage: 'Are you sure want to delete?', // confirm delete message
            minLimit: 1, // Default 1 set minimum clone HTML required
            maxLimit: 5, // Default unlimited or set maximum limit of clone HTML
            append: '<div>Hi i am appended</div>', // Set extra HTML append to clone HTML
            excludeHTML: ".exclude", // remove HTML from cloned HTML
            defaultRender: 1, // Default 1 render clone HTML
            init: function() {
                console.info(':: Initialize Plugin ::');
            },
            beforeRender: function() {
                console.info(':: Before rendered callback called');
            },
            afterRender: function() {
                console.info(':: After rendered callback called'); // Return clone object
            },
            afterRemove: function() {
                console.warn(':: After remove callback called');
            },
            beforeRemove: function() {
                console.warn(':: Before remove callback called');
            }
        });
         *
         *
         * @param: string	excludeHTML - A jQuery selector used to exclude an element and its children
         * @param: integer	maxLimit - The number of allowed copies. Default: 0 is unlimited
         * @param: string	append - HTML to attach at the end of each copy. Default: remove link
         * @param: string	copyClass - A class to attach to each copy
         * @param: boolean	clearInputs - Option to clear each copies text input fields or textarea
         *
         */

        (function($) {

            $.fn.cloneData = function(options, callback) {

                var settings = jQuery.extend({
                    mainContainerId: "clone-container",
                    cloneContainer: "clone-item",
                    excludeHTML: ".exclude",
                    emptySelector: ".empty",
                    copyClass: "clone-div",
                    removeButtonClass: "remove-item",
                    removeConfirm: false,
                    removeConfirmMessage: 'Are you sure want to delete?',
                    append: '',
                    template: null,
                    clearInputs: true,
                    maxLimit: 0, // 0 = unlimited
                    minLimit: 1, // 0 = unlimited
                    minLimitAlert: '', // 0 = unlimited
                    defaultRender: true, // true = render/initialize one clone
                    counterIndex: 0,
                    select2InitIds: [],
                    ckeditorIds: [],
                    regexID: /^(.+?)([-\d-]{1,})(.+)$/i,
                    regexName: /(^.+?)([\[\d{1,}\]]{1,})(\[.+\]$)/i,
                    init: function() {},
                    complete: function() {},
                    beforeRender: function() {},
                    afterRender: function() {},
                    beforeRemove: function() {},
                    afterRemove: function() {},
                }, options);

                if (typeof callback === 'function') { // make sure the after callback is a function
                    callback.call(this); // brings the scope to the after callback
                }

                // call the beforeRender and apply the scope:
                //console.log('init called from library'+ $('#' + settings.mainContainerId).find('.'+settings.cloneContainer).length);
                settings.init.call({index: settings.counterIndex});

                var _addItem = function () {

                    settings.counterIndex = $('.' + settings.cloneContainer).length;
                    settings.beforeRender.call(this);

                    var item_exists = $('.' + settings.cloneContainer).length;

                    // stop append HTML if maximum limit exceed
                    if (settings.maxLimit != 0 && item_exists >= settings.maxLimit){
                        alert("More than "+ settings.maxLimit +" degrees can\'t be added in one form. Please 'Add New'.");
                        return false;
                    }

                    $('#' + settings.mainContainerId).append(settings.template.first()[0].outerHTML);

                    _initializePlugins();
                    _updateAttributes();

                    // afterRender.apply(this, Array.prototype.slice.call(arguments, 1));
                    //$(settings.template.first()[0].outerHTML).trigger('afterRender');
                    ///$elem.closest('.' + widgetOptions.widgetContainer).triggerHandler(events.limitReached, widgetOptions.limit);

                    settings.afterRender.call({index: settings.counterIndex});
                    return false;
                }

                var _updateAttributes = function () {

                    $('.' + settings.cloneContainer).each(function(index) {
                        $(this).find('*').each(function() {
                            _updateAttrID($(this), index);
                            _updateAttrName($(this), index);
                        });
                    });

                    $('#' + settings.mainContainerId).addClass('clone-data');
                    $('#' + settings.mainContainerId + ' .' + settings.cloneContainer).each(function(parent_index, item){
                        $(this).attr('data-index', parent_index).addClass(settings.copyClass);
                    });


                    $('.' + settings.cloneContainer + '.' + settings.copyClass).each(function(parent_index, item) {
                        $(item).find('[for]').each(function(){
                            $(this).attr('for', $(this).attr('for').replace(/.$/, parent_index));
                        });

                        settings.complete({index: settings.counterIndex});

                    });
                }

                var _updateAttrID = function($elem, index) {
                    //var widgetOptions = eval($elem.closest('div[data-dynamicform]').attr('data-dynamicform'));
                    var id            = $elem.attr('id');
                    var newID         = id;

                    if (id !== undefined) {
                        newID = _incrementLastNumber(id, index);
                        $elem.attr( 'id', newID);
                    }

                    if (id !== newID) {
                        $elem.closest('.'+settings.cloneContainer).find('.field-' + id).each(function() {
                            $(this).removeClass('field-' + id).addClass('field-' + newID);
                        });
                        // update "for" attribute
                        $elem.closest('.'+settings.cloneContainer).find("label[for='" + id + "']").attr('for',newID);
                    }

                    return newID;
                }

                var _incrementLastNumber = function (string, index) {
                    return string.replace(/[0-9]+(?!.*[0-9])/, function(match) {
                        return index;
                    });
                }

                var _updateAttrName = function($elem, index) {
                    var name = $elem.attr('name');

                    if (name !== undefined) {
                        var matches = name.match(settings.regexName);

                        if (matches && matches.length === 4) {
                            matches[2] = matches[2].replace(/\]\[/g, "-").replace(/\]|\[/g, '');
                            var identifiers = matches[2].split('-');
                            identifiers[0] = index;

                            if (identifiers.length > 1) {
                                var widgetsOptions = [];
                                $elem.parents('.'+settings.mainContainerId).each(function(i){
                                    widgetsOptions[i] = eval($(this).find('#'+settings.mainContainerId));
                                });

                                widgetsOptions = widgetsOptions.reverse();
                                for (var i = identifiers.length - 1; i >= 1; i--) {
                                    identifiers[i] = $elem.closest('#'+settings.mainContainerId).index();
                                }
                            }

                            name = matches[1] + '[' + identifiers.join('][') + ']' + matches[3];
                            $elem.attr('name', name);
                        }
                    }

                    return name;
                };

                var _parseTemplate = function() {
                    var template_clone = $('#' + settings.mainContainerId +' .' + settings.cloneContainer + ":first");

                    var $template = $(template_clone).clone(false, false);
                    //console.log($template);

                    $template.find('input, textarea, select').each(function() {
                        if ($(this).is(':checkbox') || $(this).is(':radio')) {
                            var type         = ($(this).is(':checkbox')) ? 'checkbox' : 'radio';
                            var inputName    = $(this).attr('name');
                            var $inputHidden = $template.find('input[type="hidden"][name="' + inputName + '"]').first();
                            var count        = $template.find('input[type="' + type +'"][name="' + inputName + '"]').length;

                            if ($inputHidden && count === 1) {
                                $(this).val(1);
                                $inputHidden.val(0);
                            }

                            //$(this).prop('checked', false);
                            $(this).removeAttr("checked");
                        } else if($(this).is('select')) {
                            $(this).find('option:selected').removeAttr("selected");
                        } else if($(this).is('file')) {
                            $(this).parents('.fileinput').find('.previewing').attr('src', SITE_CONSTANT['DEFAULT_IMAGE_ADMIN']);
                            $(this).parents('.fileinput').find('.fileinput-preview img').attr('src', SITE_CONSTANT['DEFAULT_IMAGE_ADMIN']);
                            $(this).parents('.fileinput').find('.check-file-remove').hide();
                            $(this).parents('.fileinput').find('.check-file-change').hide();
                            $(this).parents('.fileinput').find('.check-file-select').show();
                        } else if($(this).is('textarea')) {
                            $(this).html("");
                        } else {
                            //$(this).val('');
                            $(this).removeAttr("value");
                        }

                    });

                    /* Remove chosen extra html */
                    $template.find('.chosen-container').each(function(){
                        $(this).remove();
                    });

                    if($template.find('.select2-container').length > 0){
                        $template.find('.select2-container').each(function(){
                            $(this).remove();
                        });
                    }

                    $template.find('.select2-container').remove();

                    //Remove Elements with excludeHTML
                    if (settings.excludeHTML){
                        $(settings.template).find(settings.excludeHTML).remove();
                    }

                    //Empty Elements with emptySelector
                    if (settings.emptySelector){
                        $(settings.template).find(settings.emptySelector).empty();
                    }

                    /* Render default HTML container */
                    if(!settings.defaultRender){
                        /* html remove after store and remove extra HTML */
                        $('.' + option.cloneContainer + ":first").remove();
                    }

                    //$template.find('input').find('input').val('');

                    //console.log($template.first()[0].outerHTML);
                    settings.template = $template;
                };

                var _initializePlugins = function(){
                    /* Initialize again chosen dropdown after render HTML */
                    if($('.chosen-init').length >0){
                        $('.chosen-init').each(function(){
                            $(this).chosen().trigger('chosen:update');
                        });
                    }

                    if($('.select2').length >0){
                        $('.select2').each(function(){
                            $(this).select2({ width: '100%' }).trigger('select2:update');
                        });
                    }

                    if($.fn.datepicker && $('.datepicker-init').length > 0) {
                        $('.datepicker-init').datepicker({autoclose: true});
                    }

                    if($.fn.datetimepicker && $('.datetimepicker-init').length > 0) {
                        $('.datetimepicker-init').datetimepicker({autoclose: true});
                    }

                    if ($.fn.select2 && settings.select2InitIds.length > 0) {
                        //console.warn(settings.select2InitIds);
                        $.each(settings.select2InitIds, function (index, id) {
                            $(id).select2({
                                placeholder: "Select",
                                width: "300px;",
                                allowClear: true
                            })

                        });
                        settings.select2InitIds = [];
                    }

                    if (window.CKEDITOR && settings.ckeditorIds.length > 0) {
                        $.each(settings.ckeditorIds, function (index, id) {
                            CKEDITOR.replace(id);

                            var $ids = $('[id=cke_' + id + ']');
                            if ($ids.length > 0) {
                                //console.log($ids);
                                $ids.remove();
                            }
                        });
                        settings.ckeditorIds = [];
                    }

                    if(typeof $.material !== 'undefined') {
                        $.material.init();
                    }
                }

                var _deleteItem = function($elem) {

                    var count = _count();
                    if (count > settings.minLimit) {
                        if(settings.removeConfirm){
                            if(confirm(settings.removeConfirmMessage)){
                                $elem.parents('.' + settings.cloneContainer).slideUp(function(){
                                    $(this).remove();
                                    _updateAttributes();
                                    settings.afterRemove.call(this);
                                    //_initializePlugins();
                                });
                            }
                        }
                    }else{
                        alert('you must have at least one item.');
                    }
                };

                var _count = function() {
                    return $('.' + settings.cloneContainer).closest('#' + settings.mainContainerId).find('.'+settings.cloneContainer).length;
                };


                $(document).on('click', '.' + settings.removeButtonClass, function(){
                    settings.beforeRemove.call(this);
                    _deleteItem($(this));
                });


                // loop each element
                this.each(function() {
                    $(this).click(function(){
                        _addItem();
                    });
                    _parseTemplate();
                    _updateAttributes();
                });

                return this; // return to jQuery
            };

        })(jQuery);
    </script>
    <script>
        $(document).ready(function() {
            $('a#add-more').cloneData({
                mainContainerId: 'main-container', // Main container Should be ID
                cloneContainer: 'container-item', // Which you want to clone
                removeButtonClass: 'remove-item', // Remove button for remove cloned HTML
                removeConfirm: true, // default true confirm before delete clone item
                removeConfirmMessage: 'Are you sure want to delete?', // confirm delete message
                //append: '<a href="javascript:void(0)" class="remove-item btn btn-sm btn-danger remove-social-media">Remove</a>', // Set extra HTML append to clone HTML
                minLimit: 0, // Default 1 set minimum clone HTML required
                defaultRender: 1,
                init: function () {
                    console.info(':: Initialize Plugin ::');
                },
                beforeRender: function () {
                    console.info(':: Before rendered callback called');
                },
                afterRender: function () {
                    console.info(':: After rendered callback called');
                    //$(".selectpicker").selectpicker('refresh');
                },
                afterRemove: function () {
                    console.warn(':: After remove callback called');
                },
                beforeRemove: function () {
                    console.warn(':: Before remove callback called');
                }

            });
        });
    </script>
@endpush
