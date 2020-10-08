import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {ProjectsComponent} from './projects/projects.component';
import {BuilderSettingsComponent} from './settings/builder/builder-settings.component';
import {CrupdateTemplateModalComponent} from './templates/crupdate-template-modal/crupdate-template-modal.component';
import {TemplatesComponent} from './templates/templates.component';
import {AppAdminRoutingModule} from './app-admin-routing.module';
import {BaseAdminModule} from '../../common/admin/base-admin.module';
import {MatProgressBarModule} from '@angular/material';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        AppAdminRoutingModule,
        BaseAdminModule,

        // material
        MatProgressBarModule,
    ],
    declarations: [
        TemplatesComponent,
        CrupdateTemplateModalComponent,
        ProjectsComponent,
        BuilderSettingsComponent,
    ],
    entryComponents: [
        CrupdateTemplateModalComponent,
    ]
})
export class AppAdminModule {
}
