@extends('layouts.main')

@section('content')
<script>
    $(document).ready(function() {
        $('#subject_areas').select2();
        $('#learning_resources_types').select2();
        $('#educational_use').select2();
        $("#keywords").select2({
            tags: true
        });

        $('#subject_areas').val({{ $resourceSubjectAreas }});
        $('#learning_resources_types').val({{ $resourceLearningResourceTypes }});
        $('#educational_use').val({{ $EditEducationalUse }});
        //$('#keywords').val({{ $resourceKeywords }});

        $('#subject_areas').trigger('change'); // Notify any JS components that the value changed
        $('#learning_resources_types').trigger('change'); // Notify any JS components that the value changed
        $('#educational_use').trigger('change'); // Notify any JS components that the value changed
        //$('#keywords').trigger('change'); // Notify any JS components that the value changed
    });
</script>
<section class="ddl-forms">
    <header>
        <h1>Add a new Resource - Step 2</h1>
    </header>
    <div class="content-body">
        <form method="POST" action="{{ route('edit2', $resource["resourceid"]) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-item">
            <label for="attachments"> 
                <strong>Attachments</strong>
            </label>
        <input class="form-control{{ $errors->has('attachments') ? ' is-invalid' : '' }}" id="attachments" name="attachments[]" size="40" maxlength="40" type="file">
            <button type='button' class="add_more">Add More Files</button>
            @if(isset($resource['attc']))
            <?php  $i = 0; ?>
            @foreach($resource['attc'] as $item)
                <br><a href="{{ asset('/storage/attachments/'.$item['file_name']) }}">{{ $item['file_name'] }}</a>
                <?php  $i++; ?>
            @endforeach
            @endif

            @if ($errors->has('attachments'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('attachments') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
            <label for="subject_areas"> 
                <strong>Subject Areas</strong>
                <span class="form-required" title="This field is required.">*</span>
            </label>
            <select class="form-control{{ $errors->has('subject_areas') ? ' is-invalid' : '' }}" id="subject_areas" name="subject_areas[]" required  multiple="multiple">
                @foreach ($subjects AS $item)
                    <option value="{{ $item->tid }}">{{ $item->name }}</option>
                @endforeach
            </select>

            @if ($errors->has('subject_areas'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('subject_areas') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
            <label for="keywords"> 
                <strong>Keywords</strong>
            </label>
            <select class="form-control{{ $errors->has('subject_areas') ? ' is-invalid' : '' }}" id="keywords" name="keywords[]"  multiple="multiple">
                    <option value=""></option>
            </select>
        </div>
        <div class="form-item">
            <label for="learning_resources_types"> 
                <strong>Learning Resources Types</strong>
                <span class="form-required" title="This field is required.">*</span>
            </label>
            <select class="form-control{{ $errors->has('learning_resources_types') ? ' is-invalid' : '' }}" id="learning_resources_types" name="learning_resources_types[]" required  multiple="multiple">
                @foreach ($learningResourceTypes AS $item)
                    <option value="{{ $item->tid }}">{{ $item->name }}</option>
                @endforeach
            </select>

            @if ($errors->has('learning_resources_types'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('learning_resources_types') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
            <label for="educational_use"> 
                <strong>Educational Use</strong>
                <span class="form-required" title="This field is required.">*</span>
            </label>
            <select class="form-control{{ $errors->has('educational_use') ? ' is-invalid' : '' }}" id="educational_use" name="educational_use[]" required  multiple="multiple">
                @foreach ($educationalUse AS $item)
                    <option value="{{ $item->tid }}">{{ $item->name }}</option>
                @endforeach
            </select>

            @if ($errors->has('educational_use'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('educational_use') }}</strong>
                </span><br>
            @endif
        </div>

        <div class="form-item">
            <label for="resource_levels"> 
                <strong>Resource Levels</strong>
                <span class="form-required" title="This field is required.">*</span>
            </label>
            <ul>
            <?php
                if(!isset($resource['level'])){
                    $resource['level'] = [];
                }
            ?>
            @foreach($levels AS $level)
                @if($level->parent == 0)
                    <li><input type="checkbox" name="level[]" {{ in_array($level->tid, $resourceLevels) ? "checked" : ""}} value="{{ $level->tid }}" onchange="fnTest(this,'subLevel{{$level->tid}}');">{{ $level->name }}
                        <?php $levelParent = $levels->where('parent', $level->tid);?>
                        @if(count($levelParent) > 0)
                            <i class="fas fa-plus fa-xs" onclick="javascript:showHide(this,'subLevel{{$level->tid}}')"></i>
                        @endif
                    @if(count($levelParent) > 0)
                        <ul id="subLevel{{$level->tid}}" class="subItem" style="display:none;">
                            @foreach($levelParent as $item)
                                <li><input type="checkbox" name="level[]" onchange="fnTest(this,'subLevel{{$item->tid}}');" {{ in_array($item->tid, $resourceLevels) ?"checked":""}} class="child" value="{{ $item->tid }}">{{ $item->name }}
                            
                                <?php $levelItemParent = $levels->where('parent', $item->tid);?>
                                @if(count($levelItemParent) > 0)
                                    <i class="fas fa-plus fa-xs" onclick="javascript:showHide(this,'subLevel{{$item->tid}}')"></i>
                                @endif
                                @if(count($levelItemParent) > 0)
                                    <ul id="subLevel{{$item->tid}}" class="subItem" style="display:none;">
                                        @foreach($levelItemParent as $itemLevel)
                                            <li><input type="checkbox" name="level[]" {{ in_array($itemLevel->tid, $resourceLevels) ?"checked":""}} class="child" value="{{ $itemLevel->tid }}">{{ $itemLevel->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
                @endif
            @endforeach
            </ul>
        </div>
        <div style="display:flex;">
            <input style="margin-right: 10px;" class="form-control normalButton" type="button" value="Previous" onclick="location.href='{{ route('edit1', $resource["resourceid"]) }}'">
            <input class="form-control normalButton" type="submit" value="Next">
        </div>
        </form>
    </div>
</section>
@endsection