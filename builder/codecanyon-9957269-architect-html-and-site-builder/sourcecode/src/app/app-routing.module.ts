import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {LoginComponent} from "common/auth/login/login.component";
import {GuestGuard} from "common/guards/guest-guard.service";
import {ContactComponent} from '../common/contact/contact.component';

const routes: Routes = [
    {path: '', component: LoginComponent, canActivate: [GuestGuard]},
    {path: 'builder', loadChildren: 'app/html-builder/html-builder.module#HtmlBuilderModule'},
    {path: 'admin', loadChildren: 'app/admin/app-admin.module#AppAdminModule'},
    {path: 'billing', loadChildren: 'common/billing/billing.module#BillingModule'},
    {path: 'contact', component: ContactComponent},
];

@NgModule({
    imports: [RouterModule.forRoot(routes)],
    exports: [RouterModule]
})
export class AppRoutingModule {
}
