<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public function index()
    {
        $myResources = new Resource();
        $resources = $myResources->paginateResources();
        return view('admin.resources',compact('resources'));
    }

    public function viewResource($resourceId)
    {
        $myResources = new Resource();
        $resource = Resource::resources()->where('resourceid',$resourceId)->first();
        $resourceLevels = $myResources->resourceAttributes($resourceId,'resources_levels','resource_level');
        $resourceAuthors = $myResources->resourceAttributes($resourceId,'resources_authors','author_name');
        $resourceAttachments = $myResources->resourceAttributes($resourceId,'resources_attachments','file_name'); 
        $resourceSubjectAreas = $myResources->resourceAttributes($resourceId,'resources_subject_areas','subject_area');
        $resourceLearningResourceTypes = $myResources->resourceAttributes($resourceId,'resources_learning_resource_types','learning_resource_type');
        $resourcePublishers = $myResources->resourceAttributes($resourceId,'resources_publishers','publisher_name');
        return view('admin.resources.view_resource', compact(
            'resource',
            'resourceLevels',
            'resourceAuthors',
            'resourceAttachments',
            'resourceSubjectAreas',
            'resourceLearningResourceTypes',
            'resourcePublishers'
        ));
    }
}