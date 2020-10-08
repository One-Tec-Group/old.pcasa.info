import {ComponentRef, ElementRef, Injectable} from '@angular/core';
import {Overlay, OverlayRef} from "@angular/cdk/overlay";
import {ComponentPortal} from "@angular/cdk/portal";
import {LinkEditorComponent} from "./link-editor.component";

@Injectable({
    providedIn: 'root'
})
export class LinkEditor {

    /**
     * Reference to currently open inline text editor overlay.
     */
    public overlayRef: OverlayRef;

    /**
     * InlineTextEditor Constructor.
     */
    constructor(private overlay: Overlay) {}

    /**
     * Open link editor for specified node.
     */
    public open(node: HTMLLinkElement) {
        const strategy = this.overlay.position().connectedTo(
            new ElementRef(node),
            {originX: 'center', originY: 'top'},
            {overlayX: 'center', overlayY: 'bottom'}
        ).withFallbackPosition(
            {originX: 'center', originY: 'bottom'},
            {overlayX: 'center', overlayY: 'top'}
        ).withFallbackPosition(
            {originX: 'end', originY: 'bottom'},
            {overlayX: 'end', overlayY: 'top'}
        ).withOffsetX(380);

        if (this.overlayRef) this.overlayRef.dispose();

        this.overlayRef = this.overlay.create({positionStrategy: strategy, hasBackdrop: true, panelClass: 'link-editor-panel'});
        const componentRef = this.overlayRef
            .attach(new ComponentPortal(LinkEditorComponent)) as ComponentRef<LinkEditorComponent>;

        componentRef.instance.setParams(node, this.overlayRef);

        this.overlayRef.backdropClick().subscribe(() => {
            this.overlayRef.dispose();
        });
    }

    /**
     * Close currently open inline text editor.
     */
    public close() {
        this.overlayRef && this.overlayRef.dispose();
    }
}
