import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {HtmlBuilderComponent} from './html-builder/html-builder.component';
import {HtmlBuilderRoutingModule} from './html-builder-routing.module';
import { InspectorComponent } from './inspector/inspector.component';
import { LivePreviewComponent } from './live-preview/live-preview.component';
import {LivePreview} from './live-preview.service';
import {PreviewDragAndDropDirective} from './live-preview/drag-and-drop/preview-drag-and-drop.directive';
import {Elements} from './elements/elements.service';
import { ElementsPanelComponent } from './inspector/elements-panel/elements-panel.component';
import {CoreModule} from 'common/core/core.module';
import {Inspector} from './inspector/inspector.service';
import {UndoManager} from './undo-manager/undo-manager.service';
import { InspectorPanelComponent } from './inspector/inspector-panel/inspector-panel.component';
import { AttributesPanelComponent } from './inspector/inspector-panel/attributes-panel/attributes-panel.component';
import { SpacingPanelComponent } from './inspector/inspector-panel/spacing-panel/spacing-panel.component';
import { BorderStyleControlsComponent } from './inspector/inspector-panel/border-style-controls/border-style-controls.component';
import { SideControlBorderComponent } from './inspector/inspector-panel/spacing-panel/side-control-border/side-control-border.component';
import { TextStylePanelComponent } from './inspector/inspector-panel/text-style-panel/text-style-panel.component';
import { BackgroundPanelComponent } from './inspector/inspector-panel/background-panel/background-panel.component';
import { GradientBackgroundPanelComponent } from './inspector/inspector-panel/background-panel/gradient-background-panel/gradient-background-panel.component';
import {InspectorFloatingPanel} from './inspector/inspector-floating-panel.service';
import { GoogleFontsPanelComponent } from './inspector/inspector-panel/text-style-panel/google-fonts-panel/google-fonts-panel.component';
import { ImageBackgroundPanelComponent } from './inspector/inspector-panel/background-panel/image-background-panel/image-background-panel.component';
import { ShadowsPanelComponent } from './inspector/inspector-panel/shadows-panel/shadows-panel.component';
import {ElementsApi} from './elements/elements-api.service';
import {CustomElementsResolver} from './elements/custom-elements-resolver.service';
import { DragVisualHelperComponent } from './live-preview/drag-and-drop/drag-visual-helper/drag-visual-helper.component';
import { LayoutPanelComponent } from './inspector/layout-panel/layout-panel.component';
import { ColumnPresetsComponent } from './inspector/layout-panel/column-presets/column-presets.component';
import {LayoutPanel} from './inspector/layout-panel/layout-panel.service';
import {DragElementsDirective} from './live-preview/drag-and-drop/drag-elements.directive';
import { InlineTextEditorComponent } from './live-preview/inline-text-editor/inline-text-editor.component';
import {InlineTextEditor} from './live-preview/inline-text-editor/inline-text-editor.service';
import { CodeEditorComponent } from './live-preview/code-editor/code-editor.component';
import {CodeEditor} from './live-preview/code-editor/code-editor.service';
import {ActiveProject} from './projects/active-project';
import { LivePreviewContextMenuComponent } from './live-preview/live-preview-context-menu/live-preview-context-menu.component';
import {ContextBoxes} from './live-preview/context-boxes.service';
import {SelectedElement} from './live-preview/selected-element.service';
import {BuilderDocument} from './builder-document.service';
import { PagesPanelComponent } from './inspector/pages-panel/pages-panel.component';
import {ProjectResolver} from './project-resolver.service';
import {BuilderDocumentActions} from './builder-document-actions.service';
import { SettingsPanelComponent } from './inspector/settings-panel/settings-panel.component';
import { TemplatesPanelComponent } from './inspector/settings-panel/templates-panel/templates-panel.component';
import { ThemesPanelComponent } from './inspector/settings-panel/themes-panel/themes-panel.component';
import { ContextBoxComponent } from './live-preview/context-box/context-box.component';
import { LinkEditorComponent } from './live-preview/link-editor/link-editor.component';
import {LinkEditor} from './live-preview/link-editor/link-editor.service';
import {InspectorDrawer} from './inspector/inspector-drawer.service';
import {LivePreviewLoader} from './live-preview/live-preview-loader.service';
import {Themes} from './themes.service';
import {DragVisualHelper} from './live-preview/drag-and-drop/drag-visual-helper/drag-visual-helper.service';
import { DeviceSwitcherComponent } from './inspector/device-switcher/device-switcher.component';
import {Projects} from '../shared/projects/projects.service';
import {MaterialModule} from '../material.module';
import {SharedModule} from '../shared/shared.module';
import {
    MatChipsModule,
    MatDialogModule,
    MatExpansionModule,
    MatRadioModule,
    MatSidenavModule,
    MatSliderModule,
    MatTabsModule
} from '@angular/material';
import {PortalModule} from '@angular/cdk/portal';
import {OverlayModule} from '@angular/cdk/overlay';
import {Templates} from '../shared/templates/templates.service';
import {ColorPickerModule} from '../../common/core/ui/color-picker/color-picker.module';
import {OverlayPanel} from '../../common/core/ui/overlay-panel/overlay-panel.service';
import {ContextMenu} from '../../common/core/ui/context-menu/context-menu.service';
import {DragDropModule} from '@angular/cdk/drag-drop';
import {ReorderLayoutItemsDirective} from './inspector/layout-panel/reorder-layout-items.directive';
import { ElementResizerDirective } from './live-preview/drag-and-drop/element-resizer.directive';

@NgModule({
    imports: [
        CommonModule,
        CoreModule,
        SharedModule,
        MaterialModule,
        HtmlBuilderRoutingModule,
        ColorPickerModule,

        // material
        MatSidenavModule,
        MatExpansionModule,
        MatSliderModule,
        MatChipsModule,
        PortalModule,
        OverlayModule,
        MatTabsModule,
        MatRadioModule,
        DragDropModule,
        MatDialogModule,
    ],
    declarations: [
        HtmlBuilderComponent,
        InspectorComponent,
        LivePreviewComponent,
        PreviewDragAndDropDirective,
        ElementsPanelComponent,
        InspectorPanelComponent,
        AttributesPanelComponent,
        SpacingPanelComponent,
        BorderStyleControlsComponent,
        SideControlBorderComponent,
        TextStylePanelComponent,
        BackgroundPanelComponent,
        GradientBackgroundPanelComponent,
        GoogleFontsPanelComponent,
        ImageBackgroundPanelComponent,
        ShadowsPanelComponent,
        DragVisualHelperComponent,
        LayoutPanelComponent,
        ColumnPresetsComponent,
        DragElementsDirective,
        InlineTextEditorComponent,
        CodeEditorComponent,
        LivePreviewContextMenuComponent,
        PagesPanelComponent,
        SettingsPanelComponent,
        TemplatesPanelComponent,
        ThemesPanelComponent,
        ContextBoxComponent,
        LinkEditorComponent,
        DeviceSwitcherComponent,

        ReorderLayoutItemsDirective,

        ElementResizerDirective,
    ],
    entryComponents: [
        GradientBackgroundPanelComponent,
        GoogleFontsPanelComponent,
        ImageBackgroundPanelComponent,
        InlineTextEditorComponent,
        CodeEditorComponent,
        LivePreviewContextMenuComponent,
        TemplatesPanelComponent,
        ThemesPanelComponent,
        LinkEditorComponent,
    ],
    providers: [
        LivePreview,
        Templates,
        Elements,
        Inspector,
        UndoManager,
        InspectorFloatingPanel,
        ElementsApi,
        CustomElementsResolver,
        LayoutPanel,
        InlineTextEditor,
        CodeEditor,
        ActiveProject,
        ContextBoxes,
        SelectedElement,
        BuilderDocument,
        BuilderDocumentActions,
        Projects,
        ProjectResolver,
        LinkEditor,
        InspectorDrawer,
        LivePreviewLoader,
        Themes,
        DragVisualHelper,
        OverlayPanel,
        ContextMenu,
    ]
})
export class HtmlBuilderModule {
}
