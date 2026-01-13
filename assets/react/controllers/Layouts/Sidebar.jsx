import React, { useState } from 'react';
import {
  Drawer,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Toolbar,
  Box,
  IconButton,
  Tooltip,
  Collapse,
  useTheme,
  Menu,
  MenuItem
} from '@mui/material';
import {
  Home,
  ChevronLeft,
  ChevronRight,
  ExpandLess,
  ExpandMore,
} from '@mui/icons-material';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import CONSTANTS from '../../utils/Constants';
import { useHasRole } from "../../utils/useHasRole";

const drawerWidth = CONSTANTS.drawerWidth || 280;

export default function Sidebar({ collapsed, onToggleCollapse, user }) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const hasRole = useHasRole();
  const theme = useTheme();

  // State to track which submenu is open (by id)
  const [openSubmenuId, setOpenSubmenuId] = useState(null);
  // State to track anchor element for collapsed menu popup
  const [menuAnchorEl, setMenuAnchorEl] = useState(null);

  const menuItems = [
    { id: 'dashboard', label: t('menu.dashboard'), icon: Home, path: '/', visible: true },
  ];

  // Handler for clicking a menu with children
  const handleMenuClick = (event, itemId) => {
    if (collapsed) {
      // Open popup menu for collapsed sidebar
      if (openSubmenuId === itemId) {
        // Close if already open
        setOpenSubmenuId(null);
        setMenuAnchorEl(null);
      } else {
        setOpenSubmenuId(itemId);
        setMenuAnchorEl(event.currentTarget);
      }
    } else {
      // Toggle collapse for expanded sidebar, close others
      setOpenSubmenuId((prev) => (prev === itemId ? null : itemId));
    }
  };

  // Handler to close popup menu
  const handleMenuClose = () => {
    setOpenSubmenuId(null);
    setMenuAnchorEl(null);
  };

  return (
    <Drawer
      variant="permanent"
      sx={{
        width: collapsed ? `calc(${theme.spacing(7)} + 1px)` : drawerWidth,
        flexShrink: 0,
        transition: 'width 0.3s ease',
        '& .MuiDrawer-paper': {
          width: collapsed ? `calc(${theme.spacing(7)} + 1px)` : drawerWidth,
          boxSizing: 'border-box',
          transition: 'width 0.3s ease',
          overflowX: 'hidden',
        },
      }}
    >
      <Toolbar />
      <Box sx={{ overflow: 'auto', p: collapsed ? 1 : 2 }}>
        {/* Toggle Button */}
        <Box sx={{ display: 'flex', justifyContent: collapsed ? 'center' : 'flex-end', mb: 2 }}>
          <Tooltip title={collapsed ? t('menu.expand_menu') : t('menu.collapse_menu')} placement="right">
            <IconButton
              onClick={onToggleCollapse}
              sx={{
                bgcolor: 'primary.main',
                color: 'white',
                '&:hover': {
                  bgcolor: 'primary.dark',
                },
                width: 32,
                height: 32
              }}
            >
              {collapsed ? <ChevronRight /> : <ChevronLeft />}
            </IconButton>
          </Tooltip>
        </Box>

        <List>
          {menuItems.filter(item => item.visible).map((item) => {
            const Icon = item.icon;
            if (!item.children) {
              return (
                <ListItem key={item.id} disablePadding sx={{ mb: 1 }}>
                  <Tooltip
                    title={collapsed ? item.label : ''}
                    placement="right"
                    disableHoverListener={!collapsed}
                  >
                    <ListItemButton
                      selected={window.location.pathname === item.path}
                      onClick={() => navigate(item.path)}
                      sx={{
                        borderRadius: 2,
                        minHeight: 48,
                        justifyContent: collapsed ? 'center' : 'initial',
                        px: collapsed ? 1.5 : 2.5,
                        '&.Mui-selected': {
                          backgroundColor: 'primary.main',
                          color: 'white',
                          '&:hover': {
                            backgroundColor: 'primary.dark',
                          },
                          '& .MuiListItemIcon-root': {
                            color: 'white',
                          },
                        },
                      }}
                    >
                      <ListItemIcon
                        sx={{
                          minWidth: 0,
                          mr: collapsed ? 0 : 3,
                          justifyContent: 'center',
                        }}
                      >
                        <Icon />
                      </ListItemIcon>
                      {!collapsed && (
                        <ListItemText
                          primary={item.label}
                          sx={{ opacity: 1 }}
                        />
                      )}
                    </ListItemButton>
                  </Tooltip>
                </ListItem>
              );
            } else {
              // Parent with children (submenu)
              return (
                <React.Fragment key={item.id}>
                  <ListItem disablePadding sx={{ mb: 1 }}>
                    <Tooltip
                      title={collapsed ? item.label : ''}
                      placement="right"
                      disableHoverListener={!collapsed}
                    >
                      <ListItemButton
                        onClick={(e) => handleMenuClick(e, item.id)}
                        sx={{
                          borderRadius: 2,
                          minHeight: 48,
                          justifyContent: collapsed ? 'center' : 'initial',
                          px: collapsed ? 1.5 : 2.5,
                        }}
                      >
                        <ListItemIcon
                          sx={{
                            minWidth: 0,
                            mr: collapsed ? 0 : 3,
                            justifyContent: 'center',
                          }}
                        >
                          <Icon />
                        </ListItemIcon>
                        {!collapsed && (
                          <ListItemText
                            primary={item.label}
                            sx={{ opacity: 1 }}
                          />
                        )}
                        {!collapsed && (openSubmenuId === item.id ? <ExpandLess /> : <ExpandMore />)}
                      </ListItemButton>
                    </Tooltip>
                  </ListItem>
                  {/* Submenu as collapse when expanded */}
                  <Collapse in={openSubmenuId === item.id && !collapsed} timeout="auto" unmountOnExit>
                    <List component="div" disablePadding>
                      {item.children.filter(child => child.visible).map((child) => {
                        const ChildIcon = child.icon;
                        return (
                          <ListItem key={child.id} disablePadding sx={{ pl: 4, mb: 1 }}>
                            <ListItemButton
                              selected={window.location.pathname === child.path}
                              onClick={() => navigate(child.path)}
                              sx={{
                                borderRadius: 2,
                                minHeight: 40,
                                justifyContent: 'initial',
                                px: 2.5,
                                '&.Mui-selected': {
                                  backgroundColor: 'primary.main',
                                  color: 'white',
                                  '&:hover': {
                                    backgroundColor: 'primary.dark',
                                  },
                                  '& .MuiListItemIcon-root': {
                                    color: 'white',
                                  },
                                },
                              }}
                            >
                              <ListItemIcon
                                sx={{
                                  minWidth: 0,
                                  mr: 2,
                                  justifyContent: 'center',
                                }}
                              >
                                <ChildIcon />
                              </ListItemIcon>
                              <ListItemText primary={child.label} sx={{ opacity: 1 }} />
                            </ListItemButton>
                          </ListItem>
                        );
                      })}
                    </List>
                  </Collapse>
                  {/* Submenu as popup menu when collapsed */}
                  <Menu
                    anchorEl={menuAnchorEl}
                    open={openSubmenuId === item.id && Boolean(menuAnchorEl)}
                    onClose={handleMenuClose}
                    anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
                    transformOrigin={{ vertical: 'top', horizontal: 'right' }}
                    PaperProps={{
                      style: {
                        minWidth: 180,
                      },
                    }}
                  >
                    {item.children.filter(child => child.visible).map((child) => {
                      const ChildIcon = child.icon;
                      return (
                        <MenuItem
                          key={child.id}
                          selected={window.location.pathname === child.path}
                          onClick={() => {
                            navigate(child.path);
                            handleMenuClose();
                          }}
                        >
                          <ChildIcon style={{ marginRight: 8 }} />
                          {child.label}
                        </MenuItem>
                      );
                    })}
                  </Menu>
                </React.Fragment>
              );
            }
          })}
        </List>
      </Box>
    </Drawer>
  );
}