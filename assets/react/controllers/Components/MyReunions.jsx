import React, { useState, useMemo } from "react";
import { useTranslation } from "react-i18next";
import { connect } from "react-redux";
import {
    Box,
    Paper,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TablePagination,
    TableRow,
    TableSortLabel,
    TextField,
    Typography,
    Chip,
    IconButton,
    Tooltip,
    InputAdornment,
    CircularProgress,
    Alert,
    Grid,
    MenuItem,
    Select,
    FormControl,
    InputLabel,
    Button
} from "@mui/material";
import {
    Search as SearchIcon,
    Visibility as VisibilityIcon,
    Event as EventIcon,
    Room as RoomIcon,
    Business as BusinessIcon,
    FilterList as FilterListIcon,
    Clear as ClearIcon
} from "@mui/icons-material";
import { visuallyHidden } from "@mui/utils";
import { useGetAccessibleReunionsByUserIdQuery } from "../redux/services/openApi";
import { useNavigate } from "react-router-dom";

// --- Utility Functions ---

function descendingComparator(a, b, orderBy) {
    if (b[orderBy] < a[orderBy]) {
        return -1;
    }
    if (b[orderBy] > a[orderBy]) {
        return 1;
    }
    return 0;
}

function getComparator(order, orderBy) {
    return order === "desc"
        ? (a, b) => descendingComparator(a, b, orderBy)
        : (a, b) => -descendingComparator(a, b, orderBy);
}

function stableSort(array, comparator) {
    const stabilizedThis = array.map((el, index) => [el, index]);
    stabilizedThis.sort((a, b) => {
        const order = comparator(a[0], b[0]);
        if (order !== 0) return order;
        return a[1] - b[1];
    });
    return stabilizedThis.map((el) => el[0]);
}

// Map Status ID to Label/Color (You can adjust mapping based on your Enum)
// Map Status ID to Label/Color (You can adjust mapping based on your Enum)
const getStatusConfig = (statusId, t) => {
    switch (statusId) {
        case 1:
        return { label: t("planned"), color: "info" };
        case 2:
        return { label: t("confirmed"), color: "secondary" };
        case 3:
        return { label: t("in_progress"), color: "primary" };
        case 4:
        return { label: t("completed"), color: "success" };
        case 5:
        return { label: t("cancelled"), color: "error" };
        case 6:
        return { label: t("postponed"), color: "warning" };
        default:
        return { label: `Statut ${statusId}`, color: "default" };
    }
};

const headCells = [
    { id: "objet", numeric: false, disablePadding: false, label: "Objet" },
    { id: "type", numeric: false, disablePadding: false, label: "Type" },
    { id: "dateDebut", numeric: false, disablePadding: false, label: "Date" },
    {
        id: "organisateur",
        numeric: false,
        disablePadding: false,
        label: "Organisateur",
    },
    { id: "lieu", numeric: false, disablePadding: false, label: "Lieu / Salle" },
    { id: "statut", numeric: false, disablePadding: false, label: "Statut" },
    { id: "actions", numeric: false, disablePadding: false, label: "Actions" },
];

function EnhancedTableHead(props) {
    const { t } = useTranslation(["reunions", "common"]);
    const { order, orderBy, onRequestSort } = props;
    const createSortHandler = (property) => (event) => {
        onRequestSort(event, property);
    };

    return (
        <TableHead>
        <TableRow>
            {headCells.map((headCell) => (
            <TableCell
                key={headCell.id}
                align={headCell.numeric ? 'right' : 'left'}
                padding={headCell.disablePadding ? 'none' : 'normal'}
                sortDirection={orderBy === headCell.id ? order : false}
                sx={{ fontWeight: 'bold', backgroundColor: '#f5f5f5' }}
            >
                {headCell.id !== 'actions' ? (
                <TableSortLabel
                    active={orderBy === headCell.id}
                    direction={orderBy === headCell.id ? order : 'asc'}
                    onClick={createSortHandler(headCell.id)}
                >
                    {headCell.label}
                    {orderBy === headCell.id ? (
                    <Box component="span" sx={visuallyHidden}>
                        {order === 'desc' ? t('sorted_descending') : t('sorted_ascending')}
                    </Box>
                    ) : null}
                </TableSortLabel>
                ) : (
                headCell.label
                )}
            </TableCell>
            ))}
        </TableRow>
        </TableHead>
    );
}

const MyReunions = ({ user }) => {
    const { t } = useTranslation(["reunions", "common"]);
    const navigate = useNavigate();

    // --- State ---
    const [order, setOrder] = useState("desc");
    const [orderBy, setOrderBy] = useState("dateDebut");
    const [page, setPage] = useState(0);
    const [rowsPerPage, setRowsPerPage] = useState(5);

    // Filters State
    const [filterText, setFilterText] = useState("");
    const [statusFilter, setStatusFilter] = useState("all");
    const [dateRange, setDateRange] = useState({ start: "", end: "" });

    // --- API Call ---
    const {
        data: reunionsData,
        error: errorReunions,
        isLoading: loadingReunions,
    } = useGetAccessibleReunionsByUserIdQuery(user?.id, {
        pollingInterval: 0,
        refetchOnMountOrArgChange: true,
        skip: !user?.id,
    });

    // --- Handlers ---
    const handleRequestSort = (event, property) => {
        const isAsc = orderBy === property && order === 'asc';
        setOrder(isAsc ? 'desc' : 'asc');
        setOrderBy(property);
    };

    const handleChangePage = (event, newPage) => {
        setPage(newPage);
    };

    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(parseInt(event.target.value, 10));
        setPage(0);
    };

    const handleClearFilters = () => {
        setFilterText("");
        setStatusFilter("all");
        setDateRange({ start: "", end: "" });
    };

    // --- Filtering & Sorting Logic ---
    const filteredRows = useMemo(() => {
        if (!reunionsData) return [];
        
        return reunionsData.filter((row) => {
        // 1. Text Search
        const searchStr = filterText.toLowerCase();
        const matchesText = (
            row.objet?.toLowerCase().includes(searchStr) ||
            row.type?.toLowerCase().includes(searchStr) ||
            row.organisateur?.nameFr?.toLowerCase().includes(searchStr) ||
            row.organisateur?.acronym?.toLowerCase().includes(searchStr)
        );

        // 2. Status Filter
        const matchesStatus = statusFilter === "all" || row.statut === statusFilter;

        // 3. Date Range Filter
        let matchesDate = true;
        if (dateRange.start || dateRange.end) {
            const meetingDate = new Date(row.dateDebut);
            // Reset time part for date comparison if needed, but usually exact comparison is fine
            
            if (dateRange.start) {
            const startDate = new Date(dateRange.start);
            matchesDate = matchesDate && meetingDate >= startDate;
            }
            
            if (dateRange.end) {
            const endDate = new Date(dateRange.end);
            // Set end date to end of day to include meetings on that day
            endDate.setHours(23, 59, 59); 
            matchesDate = matchesDate && meetingDate <= endDate;
            }
        }

        return matchesText && matchesStatus && matchesDate;
        });
    }, [reunionsData, filterText, statusFilter, dateRange]);

    const visibleRows = useMemo(
        () =>
        stableSort(filteredRows, getComparator(order, orderBy)).slice(
            page * rowsPerPage,
            page * rowsPerPage + rowsPerPage,
        ),
        [filteredRows, order, orderBy, page, rowsPerPage],
    );

    // --- Helpers for Cell Rendering ---
    const formatDateTime = (dateString) => {
        if (!dateString) return "-";
        const date = new Date(dateString);
        return isNaN(date.getTime()) 
            ? dateString 
            : new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium', timeStyle: 'short' }).format(date);
    };

    const getLocation = (row) => {
        if (row.salle) return row.salle.nom;
        if (row.lieu) return row.lieu;
        if (row.videoConferenceLink) return t("video_conference");
        return t("location_not_defined");
    };

    // --- Render ---
    if (loadingReunions) {
        return (
        <Box
            display="flex"
            justifyContent="center"
            alignItems="center"
            minHeight="300px"
        >
            <CircularProgress />
        </Box>
        );
    }

    if (errorReunions) {
        return (
        <Box m={2}>
            <Alert severity="error">
            {t(
                "error_loading_meetings",
            )}
            </Alert>
        </Box>
        );
    }

    return (
        <Box sx={{ width: '100%', mb: 2 }}>
        <Typography variant="h5" component="h2" gutterBottom sx={{ mb: 3, fontWeight: 'bold', color: 'primary.main' }}>
            {t("my_meetings")}
        </Typography>

        {/* --- Filter Toolbar --- */}
        <Paper sx={{ p: 2, mb: 2, backgroundColor: '#fafafa' }} elevation={1}>
            <Grid container spacing={2} alignItems="center">
            {/* Search Text */}
            <Grid item xs={12} md={4}>
                <TextField
                fullWidth
                variant="outlined"
                size="small"
                placeholder={t("search_meetings_placeholder")}
                value={filterText}
                onChange={(e) => setFilterText(e.target.value)}
                InputProps={{
                    startAdornment: (
                    <InputAdornment position="start">
                        <SearchIcon color="action" />
                    </InputAdornment>
                    ),
                }}
                />
            </Grid>

            {/* Status Select */}
            <Grid item xs={12} sm={6} md={2}>
                <FormControl fullWidth size="small">
                <InputLabel>{t("status")}</InputLabel>
                <Select
                    value={statusFilter}
                    label={t("Status")}
                    onChange={(e) => setStatusFilter(e.target.value)}
                >
                    <MenuItem value="all"><em>{t("status_all")}</em></MenuItem>
                    <MenuItem value={1}>{t("status_planned")}</MenuItem>
                    <MenuItem value={2}>{t("status_confirmed")}</MenuItem>
                    <MenuItem value={3}>{t("status_in_progress")}</MenuItem>
                    <MenuItem value={4}>{t("status_completed")}</MenuItem>
                    <MenuItem value={5}>{t("status_cancelled")}</MenuItem>
                    <MenuItem value={6}>{t("status_postponed")}</MenuItem>
                    <MenuItem value={7}>{t("status_archived")}</MenuItem>
                </Select>
                </FormControl>
            </Grid>

            {/* Date Range Start */}
            <Grid item xs={6} sm={3} md={2}>
                <TextField
                    fullWidth
                    label={t("from")}
                    type="date"
                    size="small"
                    value={dateRange.start}
                    onChange={(e) => setDateRange({...dateRange, start: e.target.value})}
                    InputLabelProps={{ shrink: true }}
                />
            </Grid>

            {/* Date Range End */}
            <Grid item xs={6} sm={3} md={2}>
                <TextField
                    fullWidth
                    label={t("to")}
                    type="date"
                    size="small"
                    value={dateRange.end}
                    onChange={(e) => setDateRange({...dateRange, end: e.target.value})}
                    InputLabelProps={{ shrink: true }}
                />
            </Grid>

            {/* Reset Button */}
            <Grid item xs={12} md={2}>
                <Button 
                    variant="outlined" 
                    color="secondary" 
                    fullWidth 
                    startIcon={<ClearIcon />}
                    onClick={handleClearFilters}
                    disabled={!filterText && statusFilter === 'all' && !dateRange.start && !dateRange.end}
                >
                    {t("clear_filters")}
                </Button>
            </Grid>
            </Grid>
        </Paper>

        {/* --- Data Table --- */}
        <Paper sx={{ width: '100%', mb: 2, p: 2 }}>
            <Box sx={{ mb: 2 }}>
                <Typography variant="subtitle2" color="textSecondary">
                    {filteredRows.length} {t("results_found")}
                </Typography>
            </Box>

            <TableContainer>
            <Table
                sx={{ minWidth: 750 }}
                aria-labelledby="tableTitle"
                size={'medium'}
            >
                <EnhancedTableHead
                order={order}
                orderBy={orderBy}
                onRequestSort={handleRequestSort}
                />
                <TableBody>
                {visibleRows.length === 0 ? (
                    <TableRow>
                        <TableCell colSpan={7} align="center" sx={{ py: 5 }}>
                            <Box display="flex" flexDirection="column" alignItems="center">
                                <FilterListIcon sx={{ fontSize: 40, color: 'text.disabled', mb: 1 }} />
                                <Typography variant="body1" color="textSecondary">
                                    {t("no_meetings_matching_criteria_found")}
                                </Typography>
                            </Box>
                        </TableCell>
                    </TableRow>
                ) : (
                    visibleRows.map((row) => {
                        const statusConfig = getStatusConfig(row.statut, t);
                        
                        return (
                            <TableRow
                            hover
                            tabIndex={-1}
                            key={row.id}
                            sx={{ '&:last-child td, &:last-child th': { border: 0 } }}
                            >
                            <TableCell component="th" scope="row">
                                <Typography variant="body2" fontWeight="500" sx={{ color: 'text.primary' }}>
                                    {row.objet}
                                </Typography>
                            </TableCell>
                            <TableCell>
                                <Chip label={row.type} size="small" variant="outlined" />
                            </TableCell>
                            <TableCell>
                                <Box display="flex" alignItems="center" gap={1}>
                                    <EventIcon fontSize="small" color="action" />
                                    <Typography variant="body2">
                                        {formatDateTime(row.dateDebut)}
                                    </Typography>
                                </Box>
                            </TableCell>
                            <TableCell>
                                <Box display="flex" alignItems="center" gap={1}>
                                    <BusinessIcon fontSize="small" color="action" />
                                    <Typography variant="body2">
                                        {row.organisateur?.acronym || row.organisateur?.nameFr || '-'}
                                    </Typography>
                                </Box>
                            </TableCell>
                            <TableCell>
                                <Box display="flex" alignItems="center" gap={1}>
                                    <RoomIcon fontSize="small" color="action" />
                                    <Typography variant="body2" noWrap sx={{ maxWidth: 150 }} title={getLocation(row)}>
                                        {getLocation(row)}
                                    </Typography>
                                </Box>
                            </TableCell>
                            <TableCell>
                                <Chip 
                                    label={statusConfig.label} 
                                    color={statusConfig.color} 
                                    size="small"
                                    sx={{ fontWeight: 'bold' }}
                                />
                            </TableCell>
                            <TableCell>
                                <Tooltip title={t("see_details")}>
                                    <IconButton 
                                        color="primary" 
                                        size="small"
                                        onClick={() => navigate(`/reunions/${row.id}`)}
                                    >
                                        <VisibilityIcon />
                                    </IconButton>
                                </Tooltip>
                            </TableCell>
                            </TableRow>
                        );
                    })
                )}
                </TableBody>
            </Table>
            </TableContainer>
            
            <TablePagination
                rowsPerPageOptions={[5, 10, 25, 50]}
                component="div"
                count={filteredRows.length}
                rowsPerPage={rowsPerPage}
                page={page}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
                labelRowsPerPage={t("row_per_page")}
            />
        </Paper>
        </Box>
    );
};

const mapStateToProps = (state) => ({
    user: state.auth.credentials,
});

const mapActionsToProps = {};

export default connect(mapStateToProps, mapActionsToProps)(MyReunions);
