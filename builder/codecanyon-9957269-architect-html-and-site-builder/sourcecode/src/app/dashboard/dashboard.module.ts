import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from "@angular/forms";
import {UiModule} from 'common/core/ui/ui.module';
import {MaterialModule} from '../material.module';
import {DashboardRoutingModule} from './dashboard-routing.module';
import {SharedModule} from '../shared/shared.module';
import {DashboardComponent} from './dashboard.component';
import {NewProjectPageComponent} from './new-project-page/new-project-page.component';
import {ProjectsResolver} from './projects-resolver.service';
import {TemplatesResolver} from './new-project-page/templates-resolver.service';
import {TemplatesInfiniteScrollDirective} from './new-project-page/templates-infinite-scroll.directive';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        MaterialModule,
        UiModule,
        DashboardRoutingModule,
        SharedModule,
    ],
    declarations: [
        DashboardComponent,
        NewProjectPageComponent,
        TemplatesInfiniteScrollDirective,
    ],
    providers: [
        ProjectsResolver,
        TemplatesResolver,
    ],
})
export class DashboardModule {
}
