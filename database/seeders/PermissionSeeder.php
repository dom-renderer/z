<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'dom-dashboard' => 'Dashboard',
            'flagged-items-dashboard' => 'Inspection Dashboard',
            'document-dashboard' => 'Document Dashboard',
            'production-dashboard' => 'Production Dashboard',
            'monthly-report-dom-checklists' => 'Monthly Reports',

            'users.index' => 'View Users',
            'users.create' => 'Create User',
            'users.store' => 'Save User',
            'users.show' => 'Show User',
            'users.edit' => 'Edit User',
            'users.update' => 'Update User',
            'users.destroy' => 'Delete User',
            'users.import' => 'Import User',
            'users.export' => 'Export User',
        
            'roles.index' => 'View Roles',
            'roles.create' => 'Create Role',
            'roles.store' => 'Save Role',
            'roles.show' => 'Show Role',
            'roles.edit' => 'Edit Role',
            'roles.update' => 'Update Role',
            'roles.destroy' => 'Delete Role',
        
            'store-types.index' => 'View Store Types',
            'store-types.create' => 'Create Store Type',
            'store-types.store' => 'Save Store Type',
            'store-types.show' => 'Show Store Type',
            'store-types.edit' => 'Edit Store Type',
            'store-types.update' => 'Update Store Type',
            'store-types.destroy' => 'Delete Store Type',
        
            'model-types.index' => 'View Model Types',
            'model-types.create' => 'Create Model Type',
            'model-types.store' => 'Save Model Type',
            'model-types.show' => 'Show Model Type',
            'model-types.edit' => 'Edit Model Type',
            'model-types.update' => 'Update Model Type',
            'model-types.destroy' => 'Delete Model Type',
        
            'stores.index' => 'View Stores',
            'stores.create' => 'Create Store',
            'stores.store' => 'Save Store',
            'stores.show' => 'Show Store',
            'stores.edit' => 'Edit Store',
            'stores.update' => 'Update Store',
            'stores.destroy' => 'Delete Store',
        
            'store-categories.index' => 'View Store Category',
            'store-categories.create' => 'Create Store Category',
            'store-categories.store' => 'Save Store Category',
            'store-categories.show' => 'Show Store Category',
            'store-categories.edit' => 'Edit Store Category',
            'store-categories.update' => 'Update Store Category',
            'store-categories.destroy' => 'Delete Store Category',

            'product-categories.index' => 'View Product Category',
            'product-categories.create' => 'Create Product Category',
            'product-categories.store' => 'Save Product Category',
            'product-categories.show' => 'Show Product Category',
            'product-categories.edit' => 'Edit Product Category',
            'product-categories.update' => 'Update Product Category',
            'product-categories.destroy' => 'Delete Product Category',
            
            'products.index' => 'View Product',
            'products.create' => 'Create Product',
            'products.store' => 'Save Product',
            'products.show' => 'Show Product',
            'products.edit' => 'Edit Product',
            'products.update' => 'Update Product',
            'products.destroy' => 'Delete Product',
        
            'corporate-office.index' => 'View Corporate Offices',
            'corporate-office.create' => 'Create Corporate Office',
            'corporate-office.store' => 'Save Corporate Office',
            'corporate-office.show' => 'Show Corporate Office',
            'corporate-office.edit' => 'Edit Corporate Office',
            'corporate-office.update' => 'Update Corporate Office',
            'corporate-office.destroy' => 'Delete Corporate Office',
        
            'departments.index' => 'View Departments',
            'departments.create' => 'Create Department',
            'departments.store' => 'Save Department',
            'departments.show' => 'Show Department',
            'departments.edit' => 'Edit Department',
            'departments.update' => 'Update Department',
            'departments.destroy' => 'Delete Department',

            'shifts.index' => 'View Shifts',
            'shifts.create' => 'Create Shift',
            'shifts.store' => 'Save Shift',
            'shifts.show' => 'Show Shift',
            'shifts.edit' => 'Edit Shift',
            'shifts.update' => 'Update Shift',
            'shifts.destroy' => 'Delete Shift',

            'documents.index' => 'View Documents',
            'documents.create' => 'Create Documents',
            'documents.store' => 'Save Documents',
            'documents.show' => 'Show Documents',
            'documents.edit' => 'Edit Documents',
            'documents.update' => 'Update Documents',
            'documents.destroy' => 'Delete Documents',

            'document-upload.index' => 'View Document Upload',
            'document-upload.create' => 'Create Document Upload',
            'document-upload.store' => 'Save Document Upload',
            'document-upload.show' => 'Show Document Upload',
            'document-upload.edit' => 'Edit Document Upload',
            'document-upload.update' => 'Update Document Upload',
            'document-upload.destroy' => 'Delete Document Upload',
        
            'checklists.index' => 'View Checklists Template',
            'checklists.create' => 'Create Checklist Template',
            'checklists.store' => 'Save Checklist Template',
            'checklists.show' => 'Show Checklist Template',
            'checklists.edit' => 'Edit Checklist Template',
            'checklists.update' => 'Update Checklist Template',
            'checklists.destroy' => 'Delete Checklist Template',
            'import.scheduling' => 'Import Scheduling',
            'multi-checklist-import' => 'Import Multiple Scheduling',
        
            'checklist-scheduling.index' => 'View Checklist Scheduling',
            'checklist-scheduling.create' => 'Create Checklist Schedule',
            'checklist-scheduling.store' => 'Save Checklist Schedule',
            'checklist-scheduling.show' => 'Show Checklist Schedule',
            'checklist-scheduling.edit' => 'Edit Checklist Schedule',
            'checklist-scheduling.update' => 'Update Checklist Schedule',
            'checklist-scheduling.destroy' => 'Delete Checklist Schedule',
        
            'scheduled-tasks.index' => 'View Scheduled Tasks',
            'scheduled-tasks.create' => 'Create Scheduled Task',
            'scheduled-tasks.store' => 'Save Scheduled Task',
            'scheduled-tasks.show' => 'Show Scheduled Task',
            'scheduled-tasks.edit' => 'Edit Scheduled Task',
            'scheduled-tasks.update' => 'Update Scheduled Task',
            'scheduled-tasks.destroy' => 'Delete Scheduled Task',
            'scheduled-tasks.bulk-delete' => 'Delete multiple Tasks',
            'reschedule-task' => 'Reschedule Task',
            'cancel-task' => 'Cancel Task',
            'task-export-excel' => 'Export Task to Excel',
            'task-export-pdf' => 'Export Task to PDF',
            'task-log' => 'View Task Log',
            'reschedules' => 'Reschedules Tasks',
        
            'reassignments.index' => 'View Re-Do',
            'reassignments.show' => 'Show Re-Do',

            'workflow-checklists.index' => 'View Workflow Checklists',
            'workflow-checklists.create' => 'Create Workflow Checklist',
            'workflow-checklists.store' => 'Save Workflow Checklist',
            'workflow-checklists.show' => 'Show Workflow Checklist',
            'workflow-checklists.edit' => 'Edit Workflow Checklist',
            'workflow-checklists.update' => 'Update Workflow Checklist',
            'workflow-checklists.destroy' => 'Delete Workflow Checklist',
        
            'sections.index' => 'View Sections',
            'sections.create' => 'Create Section',
            'sections.store' => 'Save Section',
            'sections.show' => 'Show Section',
            'sections.edit' => 'Edit Section',
            'sections.update' => 'Update Section',
            'sections.destroy' => 'Delete Section',

            'workflow-templates.index' => 'View Workflow Templates',
            'workflow-templates.create' => 'Create Workflow Template',
            'workflow-templates.store' => 'Save Workflow Template',
            'workflow-templates.show' => 'Show Workflow Template',
            'workflow-templates.edit' => 'Edit Workflow Template',
            'workflow-templates.update' => 'Update Workflow Template',
            'workflow-templates.destroy' => 'Delete Workflow Template',
        
            'workflow-assignments.index' => 'View Workflow Assignments',
            'workflow-assignments.create' => 'Create Workflow Assignment',
            'workflow-assignments.store' => 'Save Workflow Assignment',
            'workflow-assignments.show' => 'Show Workflow Assignment',
            'workflow-assignments.edit' => 'Edit Workflow Assignment',
            'workflow-assignments.update' => 'Update Workflow Assignment',
            'workflow-assignments.destroy' => 'Delete Workflow Assignment',
            'workflow-assignments.tasks-list' => 'View Workflow Tasks List',
            'workflow-assignments.tasks-view' => 'View Workflow Task Details',

            'topics.index' => 'View LMS Category',
            'topics.create' => 'Create LMS Category',
            'topics.store' => 'Save LMS Category',
            'topics.show' => 'Show LMS Category',
            'topics.edit' => 'Edit LMS Category',
            'topics.update' => 'Update LMS Category',
            'topics.destroy' => 'Delete LMS Category',

            'contents.index' => 'View LMS Content',
            'contents.create' => 'Create LMS Content',
            'contents.store' => 'Save LMS Content',
            'contents.show' => 'Show LMS Content',
            'contents.edit' => 'Edit LMS Content',
            'contents.update' => 'Update LMS Content',
            'contents.destroy' => 'Delete LMS Content',

            'content-analytics' => 'LMS Content View Analytics',

            'notification-templates.index' => 'View Notification Templates',
            'notification-templates.create' => 'Create Notification Template',
            'notification-templates.store' => 'Save Notification Template',
            'notification-templates.show' => 'Show Notification Template',
            'notification-templates.edit' => 'Edit Notification Template',
            'notification-templates.update' => 'Update Notification Template',
            'notification-templates.destroy' => 'Delete Notification Template',

            'imported-schedulings-history' => 'View History of CSV Imports',

            'settings.edit' => 'Settings Edit',
            'settings.update' => 'Settings Update',

            // Production UOM
            'production.uom.index',
            'production.uom.create',
            'production.uom.edit',
            'production.uom.delete',

            // Production category
            'production.category.index',
            'production.category.create',
            'production.category.edit',
            'production.category.delete',

            // Production Products
            'production.product.index',
            'production.product.create',
            'production.product.edit',
            'production.product.delete',

            // Production
            'production.index',
            'production.create',
            'production.delete',

            // Production Dispatch
            'production.dispatch.index',
            'production.dispatch.create',
            'production.dispatch.delete',

            // Production Expire
            'production.expire.index',
            'production.expire.create',
            'production.expire.delete',

            'production.planning',
            'production.planning-import'
            
        ];        

        $toNotBeDeleted = [];

        foreach ($permissions as $permission => $title) {
            $toNotBeDeleted[] = \Spatie\Permission\Models\Permission::updateOrCreate([
                'name' => $permission
            ],[
                'name' => $permission,
                'guard_name' => 'web',
                'title' => $title
            ])->id;
        }

        if (!empty($toNotBeDeleted)) {
            \Spatie\Permission\Models\Permission::whereNotIn('id', $toNotBeDeleted)->delete();
            \DB::table('model_has_permissions')->whereNotIn('permission_id', $toNotBeDeleted)->delete();
            \DB::table('role_has_permissions')->whereNotIn('permission_id', $toNotBeDeleted)->delete();
        }

        foreach (\App\Models\User::whereHas('roles', function ($builder) {
            $builder->where('id', \App\Helpers\Helper::$roles['admin']);
        })->get() as $user) {

            $user->update([
                'ticketit_admin' => 1
            ]);

        }
    }
}
