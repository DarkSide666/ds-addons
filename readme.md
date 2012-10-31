-------------------------------------------------------------------------------
        This is ATK4 addons pack made by Imants (aka. DarkSide)
        and it contains multiple addons for ATK4 version 4.2.2.
-------------------------------------------------------------------------------

INSTALLATION:
    Download ds-addons and put it in directory ds-addons in the root of your
    server (near atk4 and atk4-addons, but not inside it). So, you'll end up
    having ds-addons directory right in your root where index.php is located.
    This way it's safer to not accidentally delete it when you are renewing
    your atk4-addons release.

USAGE:
    Add path to this addon pack in your Frontend class like this.
    ```
    $this->pathfinder->addLocation('.',array('addons'=>'ds-addons'));
    ```

COMPONENTS (add-ons):
	* LISTERS - define new listers, for example, Lister_Tree.
	* MENU    - dropdown menu (horizontal and/or vertical). It relies on LISTERS.
	* GRIDEXT - grid with addExtendedSearch() functionality.

AUTHOR:
	Imants Horsts
