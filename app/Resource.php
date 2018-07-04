<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Config;

class Resource extends Model
{
    public function scopeResources()
    {
        $resources = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'rd.tnid',
                'users.username AS addedby',
                'rd.status',
                'rd.created',
                'rd.updated'
            )
            ->join('resources_data AS rd', 'rs.resourceid','=','rd.resourceid')
            ->join('users', 'users.id', '=', 'rd.userid')
            ->where('rd.language',Config::get('app.locale'))
            ->orderBy('rd.created','desc')
            ->get();
        
        if($resources){
            return $resources;
        }else{
            return abort(404);
        }
    }

    public function filterResources($requestArray)
    {
        $resources = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'rd.tnid',
                'users.username AS addedby',
                'rd.status',
                'rd.created',
                'rd.updated'
            )
            ->join('resources_data AS rd', 'rs.resourceid','=','rd.resourceid')
            ->join('users', 'users.id', '=', 'rd.userid')
            ->when(!empty($requestArray['title']), function($query) use($requestArray){
                return $query
                    ->where('rd.title', 'like', '%'.$requestArray['title'].'%');
            })
            ->when(isset($requestArray['status']), function($query) use($requestArray){
                return $query
                    ->where('rd.status', $requestArray['status']);
            })
            ->when(isset($requestArray['language']), function($query) use($requestArray){
                return $query
                    ->where('rd.language', $requestArray['language']);
            })
            ->orderBy('rd.created','desc')
            ->paginate(10);
        
        return $resources;
    }

    public function paginateResources()
    {
        $users = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'users.username AS author',
                'rd.status',
                'rd.updated'
            )
            ->join('resources_data AS rd', 'rs.resourceid','=','rd.resourceid')
            ->join('users', 'users.id', '=', 'rd.userid')
            ->where('rd.language',Config::get('app.locale'))
            ->orderBy('rd.created','desc')
            ->groupBy(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'users.username',
                'rd.status',
                'rd.updated',
                'rd.created'
            )
            ->paginate(32);

        return $users;
    }

    public function resourceAttributes($resourceId, $tableName, $fieldName, $staticTable)
    {
        $records = DB::table($tableName)
                ->select($staticTable.'.name', $staticTable.'.tid')
                ->join($staticTable, $staticTable.'.tid', '=', $tableName.'.'.$fieldName)
                ->where('resourceid',$resourceId)
                ->get();
        return $records;
    }

    public function searchResourceAttributes($keyword, $staticTable, $vid)
    {
        $records = DB::table($staticTable)
                ->select($staticTable.'.name AS value')
                ->where($staticTable.'.name','like','%'.$keyword.'%')
                ->where($staticTable.'.vid',$vid)
                ->get();
        return $records;
    }

    public function resourceAttributesList($tableName, $vid)
    {
        $records = DB::table($tableName)
                ->join('taxonomy_term_hierarchy AS tth', 'tth.tid','=',$tableName.'.tid')
                ->where('vid', $vid)
                ->where('language',Config::get('app.locale'))
                ->get();
        return $records;
    }

    public function totalResources()
    {
        $records = DB::table('resources AS rs')
                    ->selectRaw('rs.resourceid as totalResources')
                    ->count();
        return $records;
    }

    //Total resources based on language
    public function totalResourcesByLanguage()
    {
        $records = DB::table('resources AS rs')
                    ->select(
                        'rd.language',
                        DB::raw('count(rs.resourceid) as total')
                    )
                    ->join('resources_data AS rd', 'rd.resourceid','=','rs.resourceid')
                    ->groupBy('rd.language')
                    ->get();
        return $records;   
    }

    //Total resources based on subject area
    public function totalResourcesBySubject()
    {
        $records = DB::table('resources AS rs')
                    ->select(
                        'ttd.name',
                        'ttd.tid',
                        'rd.language',
                        DB::raw('count(rs.resourceid) as total')
                    )
                    ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
                    ->join('resources_subject_areas AS rsa','rsa.resourceid','=','rs.resourceid')
                    ->join('taxonomy_term_data AS ttd', function($join){
                        $join->on('ttd.tid','=','rsa.subject_area_tid')
                            ->where('ttd.vid', 8);
                    })
                    ->groupBy(
                        'ttd.name', 
                        'ttd.tid', 
                        'rd.language')
                    ->orderby('rd.language')
                    ->orderBy('total','DESC')
                    ->get();
        return $records;   
    }

    public function paginateResourcesBy($request){

        $subjectAreaIds = $request['subject_area'];
        $levelIds = $request['level'];
        $typeIds = $request['type'];

        $searchQuery = $request->input('search');

        $records = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'users.username AS author',
                'rd.status',
                'rd.updated',
                DB::raw('count(rf.resourceid) as totalfavorite'),
                DB::raw('count(rc.resourceid) as totalcomments'),
                DB::raw('count(rv.resourceid) as totalviews')
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->join('users', 'users.id', '=', 'rd.userid')
            ->leftJoin('resources_favorites AS rf', 'rf.resourceid', '=', 'rd.resourceid')
            ->leftJoin('resources_comments AS rc', 'rc.resourceid', '=', 'rd.resourceid')
            ->leftJoin('resources_views AS rv', 'rv.resourceid', '=', 'rd.resourceid')
            ->when(count($subjectAreaIds) > 0, function($query) use($subjectAreaIds){
                return $query->join('resources_subject_areas AS rsa', function ($join) use($subjectAreaIds) {
                    $join->on('rsa.resourceid', '=', 'rs.resourceid')
                        ->whereIn('rsa.subject_area_tid', $subjectAreaIds);
                });
            })
            ->when(count($levelIds) > 0, function($query)  use($levelIds){
                return $query->join('resources_levels AS rl', function ($join) use($levelIds) {
                    $join->on('rl.resourceid', '=', 'rs.resourceid')
                        ->whereIn('rl.resource_level_tid', $levelIds);
                });
            })
            ->when(count($typeIds) > 0, function($query)  use($typeIds){
                return $query->join('resources_learning_resource_types AS rlrt', function ($join) use($typeIds) {
                    $join->on('rlrt.resourceid', '=', 'rs.resourceid')
                            ->whereIn('rlrt.learning_resource_type_tid', $typeIds);
                    });
            })
            ->when(count($searchQuery) > 0, function($query)  use($searchQuery){
                return $query->where('rd.title','like','%'.$searchQuery.'%')
                    ->orwhere('rd.abstract', 'like' , '%'.$searchQuery.'%');
            })
            ->where('rd.language',Config::get('app.locale'))
            ->groupBy(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'users.username',
                'rd.status',
                'rd.updated'
            )
            ->paginate(32);

        return $records;    
    }

    //Total resources based on level
    public function totalResourcesByLevel()
    {
        $records = DB::table('resources AS rs')
            ->select(
                'ttd.tid', 
                'ttd.name', 
                'rd.language',
                DB::Raw('count(rs.resourceid) as total')
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->join('resources_levels AS rl','rl.resourceid','=','rs.resourceid')
            ->join('taxonomy_term_data AS ttd', function($join){
                $join->on('ttd.tid','=','rl.resource_level_tid')
                    ->where('ttd.vid', 13);
            })
            ->groupBy(
                'ttd.name', 
                'ttd.tid', 
                'rd.language')
            ->orderBy('rd.language')
            ->orderBy('total','DESC')
            ->get();
        return $records;   
    }

    //Total resources based on Resource Type
    public function totalResourcesByType()
    {
        $records = DB::table('resources AS rs')
            ->select(
                'ttd.tid',
                'ttd.name',
                'rd.language',
                DB::Raw('count(rs.resourceid) as total')
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->join('resources_learning_resource_types AS rlrt','rlrt.resourceid','=','rs.resourceid')
            ->join('taxonomy_term_data AS ttd', function($join){
                $join->on('ttd.tid','=','rlrt.learning_resource_type_tid')
                    ->where('ttd.vid', 7);
            })
            ->groupBy(
                'ttd.tid',
                'ttd.name',
                'rd.language'
            )
            ->orderby('rd.language')
            ->orderBy('total','DESC')
            ->get();
        return $records;   
    }

    //Total resources by attachment type (format)
    public function totalResourcesByFormat()
    {
        $records = DB::table('resources AS rs')
            ->select(
                'ra.file_mime', 
                'rd.language',
                DB::raw('count(rs.resourceid) as total')
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->join('resources_attachments AS ra','ra.resourceid','=','rs.resourceid')
            ->groupBy('ra.file_mime', 'rd.language')
            ->orderby('rd.language')
            ->orderBy('total','DESC')
            ->get();
        return $records;   
    }

    public function searchResources($searchQuery)
    {
        $records = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'rd.status',
                'rd.updated',
                DB::raw('count(rf.resourceid) as totalfavorite'),
                DB::raw('count(rc.resourceid) as totalcomments'),
                DB::raw('count(rv.resourceid) as totalviews')
                
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->leftJoin('resources_favorites AS rf', 'rf.resourceid', '=', 'rd.resourceid')
            ->leftJoin('resources_comments AS rc', 'rc.resourceid', '=', 'rd.resourceid')
            ->leftJoin('resources_views AS rv', 'rv.resourceid', '=', 'rd.resourceid')
            ->where('rd.title','like','%'.$searchQuery.'%')
            ->orwhere('rd.abstract', 'like' , '%'.$searchQuery.'%')
            ->where('rd.language', Config::get('app.locale'))
            ->groupBy(
                'rs.resourceid',
                'rd.language', 
                'rd.title',
                'rd.abstract',
                'rd.userid',
                'rd.status',
                'rd.updated'
            )
            ->paginate(32);

        return $records;
    }

    public function getRelatedResources($resourceId, $subjectAreas)
    {
        $ids = array();
        foreach($subjectAreas AS $item)
        {
            array_push($ids, $item->tid);
        }

        $records = DB::table('resources AS rs')
            ->select(
                'rs.resourceid',
                'rd.title',
                'rd.abstract'
            )
            ->join('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->join('resources_subject_areas AS rsa','rsa.resourceid','=','rs.resourceid')
            //not to include the record itself in the related items part
            ->where('rs.resourceid','!=', $resourceId)
            ->whereIn('rsa.subject_area_tid',$ids)
            ->limit(5)
            ->get();
        
        return $records;
    }

    public function subjectIconsAndTotal()
    {
        $records = DB::table('resources_subject_areas AS sarea')
            ->select(
                'sticons.file_name', 
                'ttd.name', 
                'sarea.subject_area_tid AS subject_area',
                DB::raw('count(sarea.subject_area_tid) AS total')
            )
            ->leftJoin('taxonomy_term_data AS ttd', function($join){
                $join->on('ttd.tid', '=', 'sarea.subject_area_tid')
                    ->where('ttd.vid', 8);
            })
            ->join('static_subject_area_icons AS sticons','sticons.said','=','ttd.tid')
            ->where('ttd.language', Config::get('app.locale'))
            ->groupBy(
                'sarea.subject_area_tid', 
                'sticons.file_name',
                'ttd.name'
            )
            ->get();
        return $records;
    }

    public function featuredCollections()
    {
        $records = DB::table('featured_collections AS fcid')
            ->select(
                'fcid.id', 
                'ttd.name', 
                'fcid.icon', 
                'ttd.language', 
                'fu.url', 
                'frt.type_id', 
                'frs.subject_id', 
                'frls.level_id'
            )
            ->leftJoin('taxonomy_term_data AS ttd', function($join){
                $join->on('ttd.tid', '=', 'fcid.name_tid')
                    ->where('ttd.vid', 21);
            })
            ->leftJoin('featured_resource_levels AS frl','frl.fcid', '=', 'fcid.id')
            ->leftJoin('featured_urls AS fu','fu.fcid', '=' ,'fcid.id')
            ->leftJoin('featured_resource_types AS frt','frt.fcid', '=', 'fcid.id')
            ->leftJoin('featured_resource_subjects AS frs', 'frs.fcid', '=', 'fcid.id')
            ->leftJoin('featured_resource_levels AS frls', 'frls.fcid', '=', 'fcid.id')
            ->where('ttd.language',Config::get('app.locale'))
            ->orderBy('fcid.id')
            ->get();

        return $records;
    }

    public function resourceAttachments($resourceId)
    {
        $records = DB::table('resources_attachments AS ra')
            ->select('*')
            ->where('ra.resourceid', $resourceId)
            ->get();
        return $records;
    }

    public function getResourceTranslations($resourceId)
    {
        $record = DB::table('resources AS rs')
            ->select(
                'rs.resourceid AS id',
                'rd.language'
            )
            ->leftJoin('resources_data AS rd','rd.resourceid','=','rs.resourceid')
            ->where('rd.tnid', $resourceId)
            ->get();
        return $record;
    }

    public function saveTheResource($resource=array())
    {
        return true;
    }

    public function insertFavorite($resourceId, $userId)
    {
        $record = DB::table('resources_favorites')
            ->where('resourceid', $resourceId)
            ->where('userid', $userId)
            ->first();

        if($record){
            DB::table('resources_favorites')
                ->where('resourceid', $resourceId)
                ->where('userid', $userId)
                ->delete();

            return "deleted";
        }else{
            $record = DB::table('resources_favorites')->insertGetId([
                'resourceId'    => $resourceId,
                'userid'        => $userId,
                'created'       => \Carbon\Carbon::now()->timestamp,
                'updated'       => \Carbon\Carbon::now()->timestamp
            ]);

            if($record){
                return "added";
            }
        }
    }

    public function insertFlag($params)
    {
        $record = DB::table('resources_flags')->insertGetId([
            'resourceId'    => $params['resourceid'],
            'userid'        => $params['userid'],
            'type'          => $params['type'],
            'details'       => $params['details'],
            'created'       => \Carbon\Carbon::now()->timestamp,
            'updated'       => \Carbon\Carbon::now()->timestamp
        ]);
          
        return $record;
    }

    public function insertComment($params)
    {
        $record = DB::table('resources_comments')->insertGetId([
            'resourceId'    => $params['resourceid'],
            'userid'        => $params['userid'],
            'comment'       => $params['comment'],
            'status'        => 0,
            'created'       => \Carbon\Carbon::now()->timestamp,
            'updated'       => \Carbon\Carbon::now()->timestamp
        ]);
          
        return $record;
    }

    public function getComments($resourceId)
    {
        $record = DB::table('resources_comments AS rc')
            ->select(
                'rc.userid',
                'users.username',
                'rc.comment',
                'rc.created'
            )
            ->join('users', 'users.id', '=', 'rc.userid')
            ->where('rc.resourceid', $resourceId)
            ->where('rc.status', 1)
            ->get();

        return $record;
    }

    public function getFavorite($resourceId)
    {
        $record = DB::table('resources_favorites AS rf')
            ->select('rf.resourceid')
            ->where('rf.userid', Auth::id())
            ->where('rf.resourceid',$resourceId)
            ->first();
        
        return $record;
    }
}
