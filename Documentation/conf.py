# -*- coding: utf-8 -*-
#

import sys
import os

# -- PHP highlighting configuration --------------------------------------------

from sphinx.highlighting import lexers
if lexers:
	from pygments.lexers.web import PhpLexer
	lexers['php'] = PhpLexer(startinline=True)

# -- General configuration -----------------------------------------------------

extensions = ['sphinx.ext.intersphinx', 'sphinx.ext.todo', 'sphinx.ext.ifconfig']

# Add any paths that contain templates here, relative to this directory.
templates_path = ['../_templates']

# The suffix of source filenames.
source_suffix = '.rst'

# The master toctree document.
master_doc = 'Index'

# General information about the project.
project = u'TYPO3 THEMES'
copyright = u'2015, TYPO3 THEMES TEAM (Kay Strobach, Thomas Deuling , Jo Hasenau)'

# The version info for the project you're documenting, acts as replacement for
# |version| and |release|, also used in various other places throughout the
# built documents.
#
# The short X.Y version.
version = '2.5'
# The full version, including alpha/beta/rc tags.
release = '2.5.x'

# Else, today_fmt is used as the format for a strftime call.
today_fmt = '%Y-%m-%d %H:%M'

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
exclude_patterns = ['_make']
exclude_trees = ['_make']

# If true, sectionauthor and moduleauthor directives will be shown in the
# output. They are ignored by default.
show_authors = False

# The name of the Pygments (syntax highlighting) style to use.
pygments_style = 'sphinx'

# -- Options for HTML output ----------------------------------------------

# The theme to use for HTML and HTML Help pages.  See the documentation for
# a list of builtin themes.
html_theme = 'default'

# Add any paths that contain custom themes here, relative to this directory.
html_theme_path = []

# Add any paths that contain custom static files (such as style sheets) here,
# relative to this directory. They are copied after the builtin static files,
# so a file named "default.css" will overwrite the builtin "default.css".
html_static_path = ['../Images']

# If true, "Created using Sphinx" is shown in the HTML footer. Default is True.
html_show_sphinx = False

# If true, "(C) Copyright ..." is shown in the HTML footer. Default is True.
html_show_copyright = False

# Output file base name for HTML help builder.
htmlhelp_basename = 'tmpl_adwdoc'


# -- Options for LaTeX output --------------------------------------------------

latex_elements = {

# The font size ('10pt', '11pt' or '12pt').
#'pointsize': '10pt',
}

# Grouping the document tree into LaTeX files. List of tuples
# (source start file, target name, title,
#  author, documentclass [howto, manual, or own class]).
latex_documents = [
    ('Index', 'tmpl_adw.tex', u'ADW Template',
     u'TYPO3 THEMES Team', 'manual'),
]

# -- Options for rst2pdf output ------------------------------------------------

# The options element is a dictionary that lets you override
# this config per-document.
# For example,
# ('index', u'MyProject', u'My Project', u'Author Name',
#  dict(pdf_compressed = True))
# would mean that specific document would be compressed
# regardless of the global pdf_compressed setting.
pdf_documents = [
    ('Index', 'tmpl_adw', u'TYPO3 THEMES',
     u'TYPO3 THEMES Team'),
]

# A comma-separated list of custom stylesheets. Example:
pdf_stylesheets = ['sphinx','kerning','a4']

# A list of folders to search for stylesheets. Example:
pdf_style_path = ['.', '_styles']

# How many levels deep should the table of contents be?
pdf_toc_depth = 9999

# Add section number to section references
pdf_use_numbered_links = False

# Background images fitting mode
pdf_fit_background_mode = 'scale'


# -- Options for manual page output ---------------------------------------

# One entry per manual page. List of tuples
# (source start file, name, description, authors, manual section).
man_pages = [
    ('Index', 'tmpl_adw', u'TYPO3 THEMES',
     [u'TYPO3 THEMES Team'], 1)
]

# If true, show URL addresses after external links.
#man_show_urls = False

# -- Options for Texinfo output -------------------------------------------

# Grouping the document tree into Texinfo files. List of tuples
# (source start file, target name, title, author,
#  dir menu entry, description, category)
texinfo_documents = [
    ('Index', 'tmpl_adw', u'TYPO3 THEMES',
     u'TYPO3 THEMES Team', 'TYPO3 THEMES', 'One line description of project.',
     'Miscellaneous'),
]

#=================================================
#
# TYPO3 codeblock BEGIN:
#
# Insert this codeblock at the end of your Sphinx
# builder configuration file 'conf.py'.
# This may enable TYPO3 specific features like
# TYPO3 themes. It makes Yaml settings files work.
#
#-------------------------------------------------

if 1 and "TYPO3 specific":

    try:
        t3DocTeam
    except NameError:
        t3DocTeam = {}

    try:
        import t3sphinx
        html_theme_path.insert(0, t3sphinx.themes_dir)
        html_theme = 'typo3sphinx'
    except:
        html_theme = 'default'

    t3DocTeam['conf_py_file'] = None
    try:
        t3DocTeam['conf_py_file'] = __file__
    except:
        import inspect
        t3DocTeam['conf_py_file'] = inspect.getfile(
            inspect.currentframe())

    t3DocTeam['conf_py_package_dir'] = os.path.abspath(os.path.dirname(
        t3DocTeam['conf_py_file']))
    t3DocTeam['relpath_to_master_doc'] = '..'
    t3DocTeam['relpath_to_logdir'] = '_not_versioned'
    t3DocTeam['path_to_logdir'] = os.path.join(
        t3DocTeam['conf_py_package_dir'],
        t3DocTeam['relpath_to_logdir'])
    t3DocTeam['pathToYamlSettings'] = os.path.join(
        t3DocTeam['conf_py_package_dir'],
        t3DocTeam['relpath_to_master_doc'], 'Settings.yml')
    try:
        t3DocTeam['pathToGlobalYamlSettings'] = \
            t3sphinx.pathToGlobalYamlSettings
    except:
        t3DocTeam['pathToGlobalYamlSettings'] = None
    if not t3DocTeam['pathToGlobalYamlSettings']:
        t3DocTeam['pathToGlobalYamlSettings'] = os.path.join(
            t3DocTeam['conf_py_package_dir'], 'GlobalSettings.yml')
    try:
        __function = t3sphinx.yamlsettings.processYamlSettings
    except:
        __function = None
    if not __function:
        try:
            import yamlsettings
            __function = yamlsettings.processYamlSettings
        except:
            __function = None
    if __function:
        __function(globals(), t3DocTeam)

#-------------------------------------------------
#
# TYPO3 codeblock END.
#
#=================================================
