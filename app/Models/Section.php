<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function scopeParents($query) {
        return $query->whereNotNull('parent_id')->where('parent_id', '!=', 0)->where('parent_id', '!=', '');
    }

    public function scopeOrphans($query) {
        return $query->whereNull('parent_id')->orWhere('parent_id', 0)->orWhere('parent_id', '');
    }

    public function scopeActive($query) {
        return $query->where('status', 1);
    }

    public function parent() {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children() {
        return $this->hasMany(self::class, 'parent_id')->with('children');
    }

    public function checklists() {
        return $this->hasMany(SectionChecklist::class, 'section_id', 'id');
    }

    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    public static function getSectionHierarchy($sId)
    {
        return self::where('id', $sId)
            ->with(['ancestors', 'descendants'])
            ->first();
    }

    public function getAllDescendants()
    {
        $descendants = collect();
        
        foreach ($this->descendants as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    public function getAllAncestors()
    {
        $ancestors = collect();
        
        if ($this->parent) {
            $ancestors->push($this->parent);
            $ancestors = $ancestors->merge($this->parent->getAllAncestors());
        }
        
        return $ancestors;
    }

    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public static function getHierarchyAsTree($sId, $onlyIds = false)
    {
        $section = self::getSectionHierarchy($sId);

        if ($onlyIds) {
            return [
                'current' => $section->id,
                'ancestors' => $section->getAllAncestors()->map(function($item) {
                    return $item->id;
                })->values(),
                'descendants' => $section->getAllDescendants()->map(function($item) {
                    return $item->id;
                })->values()
            ];
        } else {
            return [
                'current' => [
                    'id' => $section->id,
                    'name' => $section->name
                ],
                'ancestors' => $section->getAllAncestors()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name
                    ];
                })->values(),
                'descendants' => $section->getAllDescendants()->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name
                    ];
                })->values()
            ];
        }
    }

    public static function buildTreeFromIds(array $sIds)
    {
        $sections = self::whereIn('id', $sIds)
            ->with('parent')
            ->get();

        $parentIds = $sections->pluck('parent_id')
            ->filter()
            ->diff($sIds)
            ->unique();

        if ($parentIds->isNotEmpty()) {
            $parents = self::whereIn('id', $parentIds)->get();
            $sections = $sections->merge($parents);
        }

        return self::buildTree($sections);
    }

    private static function buildTree(\Illuminate\Support\Collection $sections, $parentId = null)
    {
        $tree = [];

        $nodes = $sections->where('parent_id', $parentId);

        foreach ($nodes as $node) {
            $children = self::buildTree($sections, $node->id);
            
            $tree[] = [
                'id' => $node->id,
                'name' => $node->name,
                'children' => $children,
                'parent_id' => $node->parent_id,
                'has_children' => count($children) > 0,
                'level' => self::calculateLevel($sections, $node)
            ];
        }

        return $tree;
    }

    private static function calculateLevel(\Illuminate\Support\Collection $section, Section $node)
    {
        $level = 0;
        $current = $node;

        while ($current->parent_id !== null && $current->parent_id !== 0) {
            $level++;
            $current = $section->firstWhere('id', $current->parent_id);
            if (!$current) break;
        }

        return $level;
    }

    public static $classNames = ['middle-level', 'product-dept', 'rd-dept', 'pipeline1', 'frontend1', 'yellow'];

    private static function arrayToHtmlList($array) {
        if (!is_array($array)) {
            return '';
        }
        
        $html = '<ul>';
        
        foreach ($array as $item) {
            $html .= '<li>' . htmlspecialchars($item) . '</li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }

    public static function getDescendantsTree($categoryId, $getCurrent = false)
    {
        $category = self::where('id', $categoryId)
            ->with('descendants')
            ->first();

        if (!$category) {
            return [];
        }

        if ($getCurrent) {
            $children = self::formatDescendants($category->descendants, self::$classNames[array_rand(self::$classNames)]);

            $checklistWithIdAndTitle = DynamicForm::select('id', 'name')->whereHas('sections', function($query) use ($category) {
                $query->where('section_id', $category->id);
            })->pluck('name', 'id')
            ->toArray();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'title' => self::arrayToHtmlList($checklistWithIdAndTitle),
                'checklist_ids' => array_keys($checklistWithIdAndTitle),
                'className' => self::$classNames[array_rand(self::$classNames)],
                'has_children' => !empty($children),
                'children' => $children
            ];
            
        } else {
            return self::formatDescendants($category->descendants, self::$classNames[array_rand(self::$classNames)]);
        }
    }
    
    private static function formatDescendants($descendants, $class = 'frontend1')
    {
        $result = [];

        foreach ($descendants as $descendant) {

            $checklistWithIdAndTitle = DynamicForm::select('id', 'name')->whereHas('sections', function($query) use ($descendant) {
                $query->where('section_id', $descendant->id);
            })->pluck('name', 'id')
            ->toArray();

            $result[] = [
                'id' => $descendant->id,
                'name' => $descendant->name,
                'className' => $class,
                'title' => self::arrayToHtmlList($checklistWithIdAndTitle),
                'checklist_ids' => array_keys($checklistWithIdAndTitle),
                'has_children' => $descendant->descendants->isNotEmpty(),
                'children' => self::formatDescendants($descendant->descendants, self::$classNames[array_rand(self::$classNames)])
            ];
        }

        return $result;
    }





    public static function getTreeForWorkflow($categoryId, $getCurrent = false)
    {
        $category = self::where('id', $categoryId)
            ->with('descendants')
            ->first();

        if (!$category) {
            return [];
        }

        if ($getCurrent) {
            $children = self::getTreeBranchesForWorkflow($category->descendants, self::$classNames[array_rand(self::$classNames)]);

            $checklistWithIdAndTitle = SectionChecklist::select('checklist_id')
                                    ->where('section_id', $category->id)
                                    ->pluck('checklist_id')
                                    ->toArray();

            foreach ($checklistWithIdAndTitle as &$checklistWithIdAndTitleRow) {
                $checklistWithIdAndTitleRow = [
                    'id' => $checklistWithIdAndTitleRow
                ];
            }

            return [
                'id' => $category->id,
                'checklist_ids' => $checklistWithIdAndTitle,
                'children' => $children
            ];
            
        } else {
            return self::getTreeBranchesForWorkflow($category->descendants, self::$classNames[array_rand(self::$classNames)]);
        }
    }
    
    private static function getTreeBranchesForWorkflow($descendants, $class = 'frontend1')
    {
        $result = [];

        foreach ($descendants as $descendant) {

            $checklistWithIdAndTitle = SectionChecklist::select('checklist_id')
            ->where('section_id', $descendant->id)
            ->pluck('checklist_id')
            ->toArray();

            foreach ($checklistWithIdAndTitle as &$checklistWithIdAndTitleRow) {
                $checklistWithIdAndTitleRow = [
                    'id' => $checklistWithIdAndTitleRow
                ];
            }

            $result[] = [
                'id' => $descendant->id,
                'checklist_ids' => $checklistWithIdAndTitle,
                'children' => self::getTreeBranchesForWorkflow($descendant->descendants, self::$classNames[array_rand(self::$classNames)])
            ];
        }

        return $result;
    }










    public static function getDescendantsPercentage($categoryId, $wfId, $getCurrent = false)
    {
        $category = self::where('id', $categoryId)
            ->with('descendants')
            ->first();

        if (!$category) {
            return [];
        }

        if ($getCurrent) {
            $children = self::formatDescendantsPercentage($category->descendants, self::$classNames[array_rand(self::$classNames)], $wfId);

            $checklistWithIdAndTitle = DynamicForm::select('id', 'name')->whereHas('sections', function($query) use ($category) {
                $query->where('section_id', $category->id);
            })->pluck('name', 'id')
            ->toArray();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'title' => self::arrayToHtmlListWithPercentage($checklistWithIdAndTitle, $wfId),
                'checklist_ids' => array_keys($checklistWithIdAndTitle),
                'className' => self::$classNames[array_rand(self::$classNames)],
                'has_children' => !empty($children),
                'children' => $children
            ];
            
        } else {
            return self::formatDescendantsPercentage($category->descendants, self::$classNames[array_rand(self::$classNames)], $wfId);
        }
    }
    
    private static function formatDescendantsPercentage($descendants, $class = 'frontend1', $wfId)
    {
        $result = [];

        foreach ($descendants as $descendant) {

            $checklistWithIdAndTitle = DynamicForm::select('id', 'name')->whereHas('sections', function($query) use ($descendant) {
                $query->where('section_id', $descendant->id);
            })->pluck('name', 'id')
            ->toArray();

            $result[] = [
                'id' => $descendant->id,
                'name' => $descendant->name,
                'className' => $class,
                'title' => self::arrayToHtmlListWithPercentage($checklistWithIdAndTitle, $wfId),
                'checklist_ids' => array_keys($checklistWithIdAndTitle),
                'has_children' => $descendant->descendants->isNotEmpty(),
                'children' => self::formatDescendantsPercentage($descendant->descendants, self::$classNames[array_rand(self::$classNames)], $wfId)
            ];
        }

        return $result;
    }

    private static function arrayToHtmlListWithPercentage($array, $wfId) {
        if (!is_array($array)) {
            return '';
        }
        
        $html = '<ul>';
        foreach ($array as $cId => $item) {
            $final = 0;
            $total = \App\Helpers\Helper::getCountHavingKey(DynamicForm::find($cId)->schema ?? [], 'name');
            $filled = \App\Helpers\Helper::getCountHavingKey(ChecklistTask::where('workflow_id', $wfId)->where('checklist_id', $cId)->first()->data ?? [], 'name');

            try {
                $final = ($filled / $total) * 100;
            } catch (\Exception $e) {}

            $html .= '<li>' . htmlspecialchars($item) . ' - <strong> ' . number_format($final, 2) . ' % </strong> </li>';
        }
        
        $html .= '</ul>';
        
        return $html;
    }
}
