@extends('layouts.main')

@section('content')
<section class="ddl-forms">
    <header>
        <h1>@lang('Add a new Resource - Step 3')</h1>
    </header>
    <div class="content-body">
        @include('layouts.messages')
        <form method="POST" action="{{ route('edit3', $resource['id']) }}">
        @csrf
        <div class="form-item">
            <label for="translation_rights"> 
                <h2>1. @lang('Translation Rights')</h2>
            </label>
            <input type="checkbox" value="1" name="translation_rights" {{ count($dbRecords->TranslationRights)?"checked":"" }}> 
            @lang('I am providing a new translation. I have selected the license that appears on the original resource.')
            @lang('If this is not translation, please skip this question and go to #2.')
            @if ($errors->has('translation_rights'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('translation_rights') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
            <label for="educational_resource"> 
                <h2>2. @lang('Educational Resource')</h2>
            </label>
            <input type="checkbox" value="1" name="educational_resource" {{ count($dbRecords->EducationalResources)?"checked":""  }}> 
            @lang('I am submitting a resource to DDL that is already published. I have selected the license that is on the original resource.')
            @lang('If you are the original author, please skip this question and go to #3.')
            @if ($errors->has('educational_resource'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('educational_resource') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
                <label for="iam_author"> 
                    <h2>3. @lang('I am the author')</h2>
                </label>
                <input type="checkbox" value="1" name="iam_author" {{ count($dbRecords->IamAuthors)?"checked":"" }}> 
                @lang('I am the author and I am submitting my resource to DDL. I am selecting a creative commons license for my resource below.')
                @if ($errors->has('iam_author'))
                    <span class="invalid-feedback">
                        <strong>{{ $errors->first('iam_author') }}</strong>
                    </span><br>
                @endif
            </div>
        <div class="form-item">
            <label for="copyright_holder"> 
                <strong>@lang('License/Copyright Holder')</strong>
            </label>
            <input class="form-control{{ $errors->has('copyright_holder') ? ' is-invalid' : '' }}" id="copyright_holder" name="copyright_holder" size="40" maxlength="40" type="text" value="{{ count($dbRecords->CopyrightHolder)?$dbRecords->CopyrightHolder->value:"" }}">
            <div class="description">
                @lang('Please enter the name of the person or organization owning or managing rights over the resource.')
            </div>
            @if ($errors->has('copyright_holder'))
                <span class="invalid-feedback">
                    <strong>{{ $errors->first('copyright_holder') }}</strong>
                </span><br>
            @endif
        </div>
        <div class="form-item">
            <h3>@lang('Select one of these')</h3>
            <label for="creative_commons"> 
                <strong>@lang('If there is Creative Commons License on the resource, select one of these')</strong>
            </label>
            @foreach($creativeCommons AS $cc)
            <?php
                if(count($dbRecords->CreativeCommons)){
                    $cc_common = $dbRecords->CreativeCommons[0]->name;
                }else{
                    $cc_common = "";
                }
            ?>
            <input type="radio" value="{{ $cc->id }}" name="creative_commons" {{ ($cc_common == $cc->name)?"checked":"" }}>{{ $cc->name }}<br>
            @endforeach
            <div class="description">
                @lang('Unsure of which option to select?') @lang('Click here') @lang('for guidance on licensing this resource.')
            </div>
        </div>
        <div class="form-item">
            <label for="creative_commons_other"> 
                <strong>@lang('If there is no Creative Commons License on the resource, select one these:')</strong>
            </label>
            @foreach($creativeCommonsOther AS $other)
            <input type="radio" value="{{ $other->id }}" name="creative_commons_other" @if(count($dbRecords->SharePermissions)) {{ $dbRecords->SharePermissions->tid == $other->id?"checked":"" }} @endif>{{ $other->name }}<br>
            @endforeach
        </div>
        <div class="form-item">
            <label for="published"> 
                <strong>@lang('Published?')</strong>
            </label>
            <input type="radio" name="published" {{ ($resource['status'] == 0)?"checked":""}} value="0"> @lang('No')
            <input type="radio" name="published" {{ ($resource['status'] == 1)?"checked":""}} value="1"> @lang('Yes')
        </div>
        <div style="display:flex;">
            <input style="margin-right: 10px;" class="form-control normalButton" type="button" value="@lang('Previous')" onclick="location.href='{{ route('edit2', $resource['id']) }}'">
            <input class="form-control normalButton" type="submit" value="@lang('Submit')">
        </div>
        </form>
    </div>
</section>
@endsection