package com.campus.eventmanagement.controller;

import com.campus.eventmanagement.model.Event;
import com.campus.eventmanagement.service.EventService;

import jakarta.servlet.http.HttpSession;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@Controller
public class EventController {

    @Autowired
    private EventService eventService;

    @GetMapping("/")
    public String viewHome(
            @RequestParam(required = false) String keyword,
            HttpSession session,
            Model model) {

        if(session.getAttribute("role") == null) {
            return "redirect:/login";
        }

        List<Event> events;

        if(keyword != null && !keyword.isEmpty()) {
            events = eventService.searchEvents(keyword);
        }
        else {
            events = eventService.getAllEvents();
}
