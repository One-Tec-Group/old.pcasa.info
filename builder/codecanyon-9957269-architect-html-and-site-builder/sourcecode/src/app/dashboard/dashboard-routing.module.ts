import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {DashboardComponent} from "./dashboard.component";
import {ProjectsResolver} from "./projects-resolver.service";
import {NewProjectPageComponent} from "./new-project-page/new-project-page.component";
import {TemplatesResolver} from "./new-project-page/templates-resolver.service";

const routes: Routes = [
    {path: 'dashboard', component: DashboardComponent, resolve: {projects: ProjectsResolver}},
    {path: 'dashboard/projects', redirectTo: 'dashboard', pathMatch: 'full'},
    {path: 'dashboard/projects/new', component: NewProjectPageComponent, resolve: {templates: TemplatesResolver}},
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class DashboardRoutingModule {
}
